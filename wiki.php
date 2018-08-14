<?php
require("classes/Helper.php");
Helper::loadClasses();

$title = $_GET["title"];
$main_article = new Article($title, array("search_users" => true, "user_limit" => 3));
?>

<style>
    body {
        font-family: arial, sans-serif;
    }

    h1 {

    }

    h3 {
        margin-bottom: 0px;
        color: #ff6a00;
    }

    h5 {
        margin: 2px 0px;
        color: #519c18;
    }

    .counter {
        color: #888;
        font-style: italic;
    }
</style>

<h1>Editores da página <strong><?= $main_article->getTitle() ?></strong></h1>
<div style="margin-left: 20px">
    <?php
    foreach ($main_article->getUsers() as $editor) {
        $editor->searchArticles(3);
        ?>
        <div>
            <h3>
                <?= $editor->getName() ?> 
                <span class="counter"><small>
                        - Edições nesta página: <?= $editor->getNumberOfEdits() ?>
                        - Artigos únicos editados: <?= count($editor->getArticles()) ?>
                    </small></span>

            </h3>
            <div style="margin-left: 30px">
                <?php
                foreach ($editor->getArticles() as $a) {
                    $a["article"]->searchCategories();
                    ?>
                    <div>
                        <h5><?= $a["article"]->getTitle() ?> <span class="counter">(<?= $a["occurrences"] ?>)</span></h5>
                    </div>
                    <?php
                    foreach ($a["article"]->getCategories() as $cat) {
                        echo $cat->getTitle() . "<br>";
                    }
                    //$a["article"]->searchUsers(3);
                    //foreach ($a["article"]->getUsers() as $u) {
                    //    echo $u->getName() . "<br>";
                    //}
                }
                ?>
            </div>
        </div>
    <?php } ?>
</div>

<br />
Bots editores da página <strong><?= $main_article->getTitle() ?></strong>
<div style="margin-left: 20px">
    <?php foreach ($main_article->getBots() as $editor) { ?>
        <div>
            <a href="#"><?= $editor->getName() ?></a> (<?= $editor->getNumberOfEdits() ?>)
        </div>
    <?php } ?>
</div>

