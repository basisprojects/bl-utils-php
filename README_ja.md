bl-utils-php
===============
Backlog API を使ってよく使う操作を自動化する PHP スクリプト集。
Jenkins 等で呼び出して利用する。PHPから Backlog API を使うサンプルにも利用可能。

## 環境
UTF-8 を利用している Linux 上で file_get_contents が利用できる標準的な PHP であれば利用可能。
cURL は追加パッケージな事が多いのあえて利用していない。

## 準備
Backlog API を利用するために必要な次の二つを src/etc/api_settings.php に記載する。
* スペースのURL
     * https://{space_key}.backlog.jp
     * https://{space_key}.backlog.com
* 「個人設定」->「API」で生成するAPIキー
* タイムゾーン。スペースの設定はAPIに反映されないため

## スクリプト

### update_index_of_tagged_wiki.php
ツリー表示が入ってからあまり使われていない Wiki のタグを利用して、
特定のページに Wiki のインデックス一覧を生成する。

#### 引数

    php update-index-of-tagged-wiki.php PROJECT_KEY INDEX_PAGE TAG_NAME

|引数|説明|
|----|----|
|PROJECT_KEY|プロジェクトキー|
|INDEX_PAGE|インデックスを作成するWikiページ名|
|TAG_NAME|収集するタグ名|

#### サンプル

    php update-index-of-tagged-wiki.php TEST_PROJECT 'インデックスページ' '開発関係'

### rotate_sprint_milestone.php
スプリントバックログをマイルストーンで管理するとバーンダウン等が連動して便利だが、
残課題を手動で移すのは手間なので古いマイルストーンにある課題を現在有効なマイルストーンに移動する。
マイルストーンに開始日と期限日を必ず設定して一日一回くらい回してればあとはその日付に従って課題が
移動していく。

注意点: 一回の課題の検索の上限100件の制限にひっかかる。
未完了の課題が100件以上あるのはスプリント計画かチーム人数に問題があると思われるが、
何回も動かしていれば残ったものも徐々に取り込まれていくので実装はシンプルにしておく。

#### 引数

    php rotate_sprint_milestone.php PROJECT_KEY MILESTONE_REGEXP

|引数|説明|
|----|----|
|PROJECT_KEY|プロジェクトキー|
|MILESTONE_REGEXP|対象マイルストーンのパターンをPHPのデリミタ付き正規表現で記述|

#### サンプル

    php rotate_sprint_milestone.php TEST_PROJECT '/^DEV_SPRINT_\d+$/'

## Author
basis
MATSUOKA Hiroshi <matsuboyjr@gmail.com>
