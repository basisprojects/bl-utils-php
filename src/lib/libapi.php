<?php
/********************************************************************************
 * HTTP methods Utilities
 **/

// Wrapper function for file_get_contents with catching errors.
function get_api_content($url, $method, $post_data){
    $context = stream_context_create(array(
        'http' => array(
            'ignore_erros' => true,
            'method' => $method,
            'header' => 'Content-Type:application/x-www-form-urlencoded',
            'content' => $post_data)));
    $http_response_header = array();
    $response = file_get_contents($url, false, $context);
    if(empty($http_response_header)){
        throw new Exception("No HTTP response.");
    }
    foreach ($http_response_header as $line){
        if(preg_match('#^HTTP/\d\.\d +200 +OK#', $line)){
            return json_decode($response, JSON_OBJECT_AS_ARRAY);
        }
    }
    $error_msg = "Not succeed. ${method} ${url}¥n";
    $error_msg += "Response Header:¥n" .  implode("¥n", $http_response_header) . "¥n";
    $error_msg .= "Response:¥n" . $response . "¥n";
    error_log($error_msg);
    throw new Exception("Not succeeded. ${url}");
}
// API with GET
function get_api($url, $query){
    if(is_array($query)){
        return get_api_content("${url}?" . http_build_query($query), 'GET', null);
    }else{
        return get_api_content("${url}?" . $query, 'GET', null);
    }
}
// API with POST
function post_api($url, $post_data){
    if(is_array($post_data)){
        return get_api_content($url, 'POST', http_build_query($post_data));
    }else{
        return get_api_content($url, 'POST', $post_data);
    }
}
// API with PATCH
function patch_api($url, $post_data){
    if(is_array($post_data)){
        return get_api_content($url, 'PATCH', http_build_query($post_data));;
    }else{
        return get_api_content($url, 'PATCH', $post_data);
    }
}
/********************************************************************************
 * Conversion Utilities
 **/
// Get formatted date from DateTime
function format_datetime($source){
    return $source->format('Y-m-d');
}
// Converting Backlog's date string with specified timezone
function convert_backlog_date($date_string, $timezone_string){
    $timezone = null;
    if($timezone_string){
        $timezone = new DateTimeZone($timezone_string);
    }
    $d = DateTime::createFromFormat('Y-m-d+', $date_string, $timezone);
    if($d === FALSE){
        throw new Exception("Cannot convert date string: $date_string");
    }
    $d->setTime(0,0);
    return $d;
}
?>
<?php
/********************************************************************************
 * Using Backlog API around Milestone features
 **/

/**
 * Get Milestones from Project.
 **/
function get_milestones($space_url, $api_key, $project_key){
    $params = array(
        'apiKey' => $api_key
    );
    return get_api("${space_url}/api/v2/projects/${project_key}/versions", $params);
}

/********************************************************************************
 * Using Backlog API around Project features
 **/

/**
 * Get Project by Project Key.
 **/
function get_project($space_url, $api_key, $project_key){
    $params = array(
        'apiKey' => $api_key
    );
    return get_api("${space_url}/api/v2/projects/${project_key}", $params);
}

/********************************************************************************
 * Using Backlog API around Status features
 **/
define('STATUS_ID_OPEN', 1);
define('STATUS_ID_PROGRESS', 2);
define('STATUS_ID_RESOLVED', 3);
define('STATUS_ID_CLOSED', 4);
/**
 * Get Statuses in Project.
 **/
function get_statuses($space_url, $api_key, $project_key){
    $params = array(
        'apiKey' => $api_key
    );
    return get_api("${space_url}/api/v2/projects/${project_key}/statuses", $params);
}
/**
 * Get Status Ids except "Closed"
 **/
function get_opened_status_ids($space_url, $api_key, $project_key){
    $statuses = get_statuses($space_url, $api_key, $project_key);
    $opened_statuses = array_reduce($statuses, function($result, $value){
        if($value['id'] != STATUS_ID_CLOSED){
            array_push($result, $value['id']);
        }
        return $result;
    }, array());
    return $opened_statuses;
}

/********************************************************************************
 * Using Backlog API around Find Issue features
 **/

/**
 * Get Issues with condition, simply.
 * This API in Backlog have a limitation, 'count' cannot set over 100.
 **/
function get_issues_100($space_url, $api_key, $condition){
    $params = array_merge($condition, array(
        'apiKey' => $api_key,
        'count' => 100
    ));
    return get_api("${space_url}/api/v2/issues", $params);
}

/********************************************************************************
 * Using Backlog API around Issue features
 **/
function update_issue($space_url, $api_key, $issue_key, $modifications){
    $params = array(
        'apiKey' => $api_key
    );
    $url = "${space_url}/api/v2/issues/${issue_key}?" . http_build_query($params);
    return patch_api($url, $modifications);
}
/********************************************************************************
 * Using Backlog API around Wiki features
 **/

/**
 * Get Wiki page's object(including its content).
 **/
function get_wiki_page($space_url, $api_key, $wiki_id){
    $params = array(
        'apiKey' => $api_key
    );
    return get_api("${space_url}/api/v2/wikis/${wiki_id}" , $params);
}
/**
 * Modify Wiki page's name and content.
 **/
function modify_wiki_content($space_url, $api_key, $wiki_id, $name, $content){
    $params = array(
        'apiKey' => $api_key
    );
    $post_data = array(
        'name' => $name,
        'content' => $content
    );
    $url = "${space_url}/api/v2/wikis/${wiki_id}?" . http_build_query($params);
    return patch_api($url, $post_data);
}
/**
 * Get all wiki objects from specified project.
 * Notice: This object don't include text content, attributes only.
 **/
function get_wiki_list($space_url, $api_key, $project_key){
    $params = array(
        'apiKey' => $api_key,
        'projectIdOrKey' => $project_key
    );
    return get_api("${space_url}/api/v2/wikis", $params);
}
?>
