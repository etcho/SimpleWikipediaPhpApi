<?php
require("classes/Helper.php");
Helper::loadClasses();

$title = $_GET["title"];
$main_article = new Article($title, array("search_users" => true, "user_limit" => 25, "only_logged_users" => true));
?>

<!DOCTYPE html>
<html>
    <head>
        <title>06. Composite Node.</title>
        <style>
            body {
                font-family: arial, sans-serif;
                font-size: 10px;
            }
        </style>
        <script type="text/javascript" src="VivaGraphJS/vivagraph.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript">
            function main() {
                // This demo shows how to create an SVG node which is a bit more complex
                // than single image. Do accomplish this we use 'g' element and
                // compose group of elements to represent a node.
                var graph = Viva.Graph.graph();

                var graphics = Viva.Graph.View.svgGraphics(),
                        nodeSize = 32,
                        highlightRelatedNodes = function (nodeId, isOn) {
                            // just enumerate all realted nodes and update link color:
                            graph.forEachLinkedNode(nodeId, function (node, link) {
                                var linkUI = graphics.getLinkUI(link.id);
                                if (linkUI) {
                                    // linkUI is a UI object created by graphics below
                                    linkUI.attr('stroke', isOn ? 'red' : 'gray');
                                }
                            });
                        };

                graph.addNode('<?= $main_article->getTitle() ?>', {type: 'article'});
<?php
$added_users = array();
$added_articles = array($main_article->getTitle());
foreach ($main_article->getUsers() as $user) {
    $user->searchArticles(2);
    $name = str_replace("'", "", $user->getName());
    if (true || !in_array($name, $added_users)) {
        ?>
                        graph.addNode('<?= $name ?>', {type: 'user'});
    <?php } ?>
                    graph.addLink('<?= $main_article->getTitle() ?>', '<?= $name ?>');
    <?php
    $added_users[] = $name;
    foreach ($user->getArticles() as $a) {
        $a["article"]->searchCategories(2);
        $title = str_replace("'", "", $a["article"]->getTitle());
        if (true || !in_array($title, $added_articles)) {
            ?>
                            //graph.addNode('<?= $title ?>', {type: 'article'});
        <?php } ?>
                        //graph.addLink('<?= $name ?>', '<?= $title ?>');
        <?php
        $added_articles[] = $title;
        foreach ($a["article"]->getCategories() as $category) {
            $cat_title = str_replace("'", "", $category->getTitle());
            ?>
                            graph.addNode('<?= $cat_title ?>', {type: 'category'});
                            graph.addLink('<?= $cat_title ?>', '<?= $name ?>');
            <?php
        }
        /* $a["article"]->searchUsers(3);
          $title = str_replace("'", "", $a["article"]->getTitle());
          foreach ($a["article"]->getUsers() as $user_1) {
          $name_1 = str_replace("'", "", $user->getName());
          echo "console.log('" . $name . " -> " . $title . " -> " . $name_1 . "');
          ";
          if (true || !in_array($name_1, $added_users)) {
          ?>
          graph.addNode('<?= $name_1 ?>', {type: 'user'});
          <?php } ?>
          graph.addLink('<?= $title ?>', '<?= $name_1 ?>');
          <?php
          $added_users[] = $name_1;
          } */
    }
}
?>
                //graph.addNode('Etcho', {type: 'user'});
                //graph.addLink('Miracema', 'Etcho');

                graphics.node(function (node) {
                    // This time it's a group of elements: http://www.w3.org/TR/SVG/struct.html#Groups
                    var ui = Viva.Graph.svg('g'),
                            // Create SVG text element with user id as content
                            svgText = Viva.Graph.svg('text').attr('y', '-4px').text(node.id),
                            img = Viva.Graph.svg('image')
                            .attr('width', nodeSize)
                            .attr('height', nodeSize)
                            .link(node.data.type == 'article' ? 'https://static.filehorse.com/icons-web/educational-software/wikipedia-icon-32.png' : (node.data.type == 'user' ? 'https://cdn0.iconfinder.com/data/icons/healthcare-medical-4/512/avatar-32.png' : 'assets/images/dot.png'));

                    ui.append(svgText);
                    ui.append(img);
                    $(ui).hover(function () { // mouse over
                        highlightRelatedNodes(node.id, true);
                    }, function () { // mouse out
                        highlightRelatedNodes(node.id, false);
                    });
                    return ui;
                }).placeNode(function (nodeUI, pos) {
                    // 'g' element doesn't have convenient (x,y) attributes, instead
                    // we have to deal with transforms: http://www.w3.org/TR/SVG/coords.html#SVGGlobalTransformAttribute
                    nodeUI.attr('transform',
                            'translate(' +
                            (pos.x - nodeSize / 2) + ',' + (pos.y - nodeSize / 2) +
                            ')');
                });

                // Render the graph
                var renderer = Viva.Graph.View.renderer(graph, {
                    graphics: graphics
                });
                renderer.run();
            }
        </script>

        <style type="text/css" media="screen">
            html, body, svg { width: 100%; height: 100%;}
        </style>
    </head>
    <body onload='main()'>

    </body>
</html>
