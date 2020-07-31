<?php
require_once(__DIR__ . '/../lib/libapi.php');
# Import setting of space and key of api.
require_once(__DIR__ . '/../etc/api_settings.php');
?>
<?php
/********************************************************************************
 * Move Issues along specified Milestones.
 * The Milestones require isolated StartDate and ReleaseDueDate,
 * as if Sprint in Scrum.
 **/

/**
 * Collect now and past Milestones which matches specified RegExp.
 **/
function collect_milestones($milestone_regexp, $current_date, $milestone_list){
    $current_milestone = null;
    $past_milestones = array();
    echo 'today: ' . format_datetime($current_date) ."\n";
    foreach ($milestone_list as $milestone){
        if(!preg_match($milestone_regexp, $milestone["name"])){
            continue;
        }
        if($milestone['archived']){
            continue;
        }
        $start_date = convert_backlog_date($milestone['startDate'], TIMEZONE);
        $due_date = convert_backlog_date($milestone['releaseDueDate'], TIMEZONE);
        if($due_date < $current_date){
            echo 'past: ' . $milestone['name']
                . ' from: ' . format_datetime($start_date) . ' to: ' . format_datetime($due_date)
                . "\n";
            array_push($past_milestones, $milestone);
            continue;
        }
        if($start_date <= $current_date && $current_date <= $due_date){
            if(is_null($current_milestone)){
                echo 'current: ' . $milestone['name'] 
                    . ' from: ' . format_datetime($start_date) . ' to: ' . format_datetime($due_date)
                    . "\n";
                $current_milestone = $milestone;
                continue;
            }else{
                throw new Exception('Found overlapped milestones.');
            }
        }
    }
    return array( $current_milestone, $past_milestones);
}

/**
 * Move issues from old Milestone ID to new Milestone ID.
 **/
function move_another_milestone($space_url, $api_key, $project_key, $issue, $old_id, $new_id){
    $milestone_ids = array();
    foreach($issue['milestone'] as $milestone){
        if($milestone['id'] == $old_id){
            array_push($milestone_ids, $new_id);
        }else{
            array_push($milestone_ids, $milestone['id']);
        }
    }
    update_issue($space_url, $api_key, $issue['issueKey'], array(
        'milestoneId' => $milestone_ids
    ));
}

/**
 * Main procedure
 **/
function rotate_sprint_milestones($project_key, $milestone_regexp, $current_date){
    $milestone_list = get_milestones(SPACE_URL, API_KEY, $project_key);
    list($current_milestone, $past_milestones) = 
        collect_milestones($milestone_regexp, $current_date, $milestone_list);
    if(is_null($current_milestone)){
        throw new Exception("Not found any milestone including " . format_datetime($current_date) . ".");
    }
    $project = get_project(SPACE_URL, API_KEY, $project_key);
    $statuses = get_opened_status_ids(SPACE_URL, API_KEY, $project_key);
    foreach($past_milestones as $milestone){
        $issues = get_issues_100(SPACE_URL, API_KEY, array(
            'projectId' => array($project['id']),
            'milestoneId' => array($milestone['id']),
            'statusId' => $statuses
        ));
        if(count($issues) == 0){
            continue;
        }

        foreach($issues as $issue){
            move_another_milestone(SPACE_URL, API_KEY, $project_key, 
                $issue, $milestone['id'], $current_milestone['id']);
        }
        echo count($issues) . ' issue(s) moved from ' . $milestone['name'] . ' to ' . $current_milestone['name'] . "\n";
    }
}

/**
 * Parameters from command line
 **/
$project_key = $argv[1]; # Project Key
$milestone_regexp = $argv[2]; # Pattern of Milestones in RegExp

$current_date = new DateTime("now", new DateTimeZone(TIMEZONE));
$current_date->setTime(0, 0);
rotate_sprint_milestones($project_key, $milestone_regexp, $current_date);
?>
