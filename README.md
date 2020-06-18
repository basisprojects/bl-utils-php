bl-utils-php
===============
These are my personal tools to automate operations on Backlog.
I expect developer use the scrips from jobs of Jenkins CI,
or samples to access Backlog API in PHP.

## Requirements
You can run the scripts on Linux with plain PHP which is installed through package.
These scripts stand on a function "file-get-contents" instead cURL, because 
on some distributions, cURL library is divided from core PHP package.

## Preparation 
Backlog API generally need three parameters below,
* URL of space like;
     * https://{space_key}.backlog.jp
     * https://{space_key}.backlog.com
* API key whic is created in "API" of "Personal Settings"
* Timezone you want to use, due to API results are not specified timezone.
You have to write those in "src/etc/api_settings.php".

## Scripts

### update_index_of_tagged_wiki.php
This creates plain index of Wiki pages related to specified Tag.
Tag feature of Backlog may not be used nowadays, though....

#### Arguments

    php update-index-of-tagged-wiki.php PROJECT_KEY INDEX_PAGE TAG_NAME

|Argument|Description|
|--------|-----------|
|PROJECT_KEY|Project Key|
|INDEX_PAGE|Wiki page's name to be written the index|
|TAG_NAME|Tag Name, you want to make the index|

#### Example

    php update-index-of-tagged-wiki.php TEST_PROJECT 'Index for Developer' 'For Developer'

### rotate_sprint_milestone.php
In my opnion, Backlog's Milestone feature can be Sprint, 
or any kind of timebox in Agile development.
If you organize Milestones with isolated "Start Date" and "End Date", 
Backlog show Burndown Chart in a Project Home automatically. It's useful.

But when undone Issues exist in past Milestone, 
you have to move them to the next Milestone manually...that's bothering much.
This script move such undone Issues to running Milestone automatically.

Notice: API have a limitaion, searching issues cannot get over 100 results.
In my experiences, a lot of undone Issues pointed wrong planning of Sprint,
or wrong size of team...
however, this limitation will be resolved by running this script repeatedly.

#### Arguments

    php rotate_sprint_milestone.php PROJECT_KEY MILESTONE_REGEXP

|Argument|Description|
|--------|-----------|
|PROJECT_KEY|Project Key|
|MILESTONE_REGEXP|Pattern of target Milestones' name in RegExp of PHP with delimiters|

#### Example

    php rotate_sprint_milestone.php TEST_PROJECT '/^DEV_SPRINT_\d+$/'

## Author
basis
MATSUOKA Hiroshi <matsuboyjr@gmail.com>
