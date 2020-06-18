<?php
require_once(__DIR__ . '/../lib/libapi.php');
# Import setting of space and key of api.
require_once(__DIR__ . '/../etc/api_settings.php');
?>
<?php
/********************************************************************************
 * Update specified Wiki page with index of tagged pages.
 * if the index was not changed before, quit updating.
 **/

/**
 * Make the index from tagged Wiki pages.
 **/
function make_index_from_tag($wiki_list, $tag_name){
    $list = array();
    foreach ($wiki_list as $page){
        foreach($page['tags'] as $tag){
            if($tag['name'] == $tag_name){
                array_push($list, $page);
            }
        }
    }
    $name_list = array();
    foreach ($list as $item){
        $name = $item['name'];
        array_push($name_list, $name);
    }
    sort($name_list, SORT_STRING);
    $index_list = array();
    foreach ($name_list as $name){
        array_push($index_list, "- [[${name}]]");
    }
    return implode("\n", $index_list);
}

/**
 * Main procedures
 **/
function update_wiki_index($project_key, $index_page, $tag_name){
    $wiki_list = get_wiki_list(SPACE_URL, API_KEY, $project_key);
    $new_index = make_index_from_tag($wiki_list, $tag_name);
    foreach($wiki_list as $page){
        if($page['name'] != $index_page){
            continue;
        }
        $wiki = get_wiki_page(SPACE_URL, API_KEY, $page['id']);
        if(strcmp($wiki['content'], $new_index) != 0){
            modify_wiki_content(SPACE_URL, API_KEY, $page['id'], $page['name'], $new_index);
            $wiki_url = SPACE_URL . "wiki/${project_key}/${index_page}";
            echo "Updated ${wiki_url}.";
        }
        break;
    }
}

/**
 * Parameters from command line
 **/
$project_key = $argv[1]; # Project Key
$index_page = $argv[2]; # Wiki page's name to create the index.
$tag_name = $argv[3]; # TagName for searching in WIki pages.

update_wiki_index($project_key, $index_page, $tag_name);
?>
