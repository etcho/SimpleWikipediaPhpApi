<?php
require("classes/Helper.php");
Helper::loadClasses();
?>

<style>
    body {
        font-family: arial, sans-serif;
        font-size: 12px;
    }

    h1 {
        font-size: 16px;
        margin: 10px 0px 0px 0px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 0px;
    }

    table, td, th {
        border-collapse: collapse;
        font-size: inherit;
    }

    td {
        vertical-align: top;
    }
</style>
<form method="post">
    <label>Title:</label>
    <input type="text" name="title" value="<?= $_POST ? $_POST["title"] : "" ?>" />
    <label style="margin-left: 20px">Número de usuários:</label>
    <input type="text" name="user_number" value="<?= $_POST ? $_POST["user_number"] : 5 ?>" />
    <button type="submit" style="margin-left: 20px">buscar</button>
</form>
<br /><br />

<?php
if ($_POST) {
    $title = $_POST["title"];
    $user_number = $_POST["user_number"];
    $article = new Article($title);
    $article->searchUsers(null, true);
    ?>

    <?php
    foreach ($article->getUsers() as $i => $user) {
        if ($i == $user_number) {
            break;
        }

        $user->searchContributions();

        $ns = array();
        $flags = array("minor" => 0, "new" => 0, "top" => 0);
        $diff = array("add" => 0, "sub" => 0);
        $ores = array("damaging_prob" => 0, "goodfaith_prob" => 0, "draftqualitities" => array(), "wp10s" => array());
        foreach ($user->getContributions() as $contribution) {
            if (!array_key_exists($contribution->getArticle()->getNs(), $ns)) {
                $ns[$contribution->getArticle()->getNs()] = 0;
            }
            $ns[$contribution->getArticle()->getNs()] ++;
            if ($contribution->getIsMinor()) {
                $flags["minor"] ++;
            }
            if ($contribution->getIsNew()) {
                $flags["new"] ++;
            }
            if ($contribution->getIsTop()) {
                $flags["top"] ++;
            }
            if ($contribution->getSizediff() >= 0) {
                $diff["add"] ++;
            } else {
                $diff["sub"] ++;
            }

            $ores_score = $contribution->getOresScore();
            $ores["damaging_prob"] += $ores_score->getDamagingProb();
            $ores["goodfaith_prob"] += $ores_score->getGoodfaithProb();
            if (count($ores_score->getDraftquality()) > 0) {
                $ores["draftqualities"][] = $ores_score->getDraftquality();
            }
            if (count($ores_score->getWp10()) == 6) {
                $ores["wp10s"][] = $ores_score->getWp10();
            }
        }

        $ores["draftquality"] = array();
        foreach ($ores["draftqualities"] as $dq) {
            foreach ($dq as $quality => $prob) {
                if (!array_key_exists($quality, $ores["draftquality"])) {
                    $ores["draftquality"][$quality] = 0;
                }
                $ores["draftquality"][$quality] += $prob;
            }
        }
        foreach ($ores["draftquality"] as $quality => $prob) {
            $ores["draftquality"][$quality] = $prob / count($ores["draftqualities"]);
        }

        $ores["wp10"] = array();
        foreach ($ores["wp10s"] as $wp10) {
            foreach ($wp10 as $quality => $prob) {
                if (!array_key_exists($quality, $ores["wp10"])) {
                    $ores["wp10"][$quality] = 0;
                }
                $ores["wp10"][$quality] += $prob;
            }
        }
        foreach ($ores["wp10"] as $quality => $prob) {
            $ores["wp10"][$quality] = $prob / count($ores["wp10s"]);
        }

        $total = count($user->getContributions());

        echo "<h2>" . $user->getName() . "</h2>";
        ?>
        <div style="margin-left: 30px">
            - Edições nesta página: <?= $user->getNumberOfEdits() ?>
            <table>
                <tr>
                    <td style="padding-right: 20px;">
                        <h1>Atividade em namespaces</h1>
                        <table border="1">
                            <tr>
                                <th>Namespace</th>
                                <th>Count</th>
                                <th>%</th>
                            </tr>
                            <?php
                            arsort($ns);
                            foreach ($ns as $namespace => $count) {
                                $percent = $count / array_sum($ns);
                                ?>
                                <tr>
                                    <td><?= Helper::convertNs($namespace) ?></td>
                                    <td style="text-align: center"><?= $count ?></td>
                                    <td style="text-align: center"><?= number_format($percent, 4) ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <th></th>
                                <th><?= array_sum($ns) ?></th>
                                <th></th>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-right: 20px;">
                        <h1>Atividade por flags</h1>
                        <table border="1">
                            <tr>
                                <th>Flag</th>
                                <th>Count</th>
                                <th>%</th>
                            </tr>
                            <?php foreach ($flags as $flag => $count) { ?>
                                <tr>
                                    <td><?= $flag ?></td>
                                    <td><?= $count ?></td>
                                    <td><?= number_format($count / array_sum($ns), 4) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                    <td style="padding-right: 20px;">
                        <h1>Atividade por diff</h1>
                        <table border="1">
                            <tr>
                                <th>Diff</th>
                                <th>Count</th>
                                <th>%</th>
                            </tr>
                            <tr>
                                <th>Add (+)</th>
                                <td><?= $diff["add"] ?></td>
                                <td><?= number_format($diff["add"] / array_sum($diff), 4) ?></td>
                            </tr>
                            <tr>
                                <th>Sub (-)</th>
                                <td><?= $diff["sub"] ?></td>
                                <td><?= number_format($diff["sub"] / array_sum($diff), 4) ?></td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-right: 20px;">
                        <h1>Ores</h1>
                        <table border="1">
                            <tr style="font-weight: bold; color: <?= $ores["damaging_prob"] / $total > 0.8 ? 'red' : ($ores["damaging_prob"] / $total > 0.3 ? 'orange' : 'green') ?>">
                                <td>Damaging prob.</td>
                                <td><?= number_format($ores["damaging_prob"] / $total, 4) ?></td>
                            </tr>
                            <tr style="font-weight: bold; color: <?= $ores["goodfaith_prob"] / $total > 0.8 ? 'green' : ($ores["goodfaith_prob"] / $total > 0.3 ? 'orange' : 'red') ?>">
                                <td>Goodfaith prob.</td>
                                <td><?= number_format($ores["goodfaith_prob"] / $total, 4) ?></td>
                            </tr>
                            <?php
                            $top_draft_quality = "";
                            $top_draft_prob = "0";
                            foreach ($ores["draftquality"] as $quality => $prob) {
                                if ($prob > $top_draft_prob) {
                                    $top_draft_prob = $prob;
                                    $top_draft_quality = $quality;
                                }
                            }
                            arsort($ores["draftquality"]);
                            foreach ($ores["draftquality"] as $quality => $prob) {
                                ?>
                                <tr<?= $quality == $top_draft_quality ? ' style="color: blue; font-weight: bold;"' : "" ?>>
                                    <td>Draft "<?= $quality ?>"</td>
                                    <td><?= number_format($prob, 4) ?></td>
                                </tr>
                                <?php
                            }
                            $top_wp10_quality = "";
                            $top_wp10_prob = "0";
                            foreach ($ores["wp10"] as $quality => $prob) {
                                if ($prob > $top_wp10_prob) {
                                    $top_wp10_prob = $prob;
                                    $top_wp10_quality = $quality;
                                }
                            }
                            arsort($ores["wp10"]);
                            foreach ($ores["wp10"] as $quality => $prob) {
                                ?>
                                <tr<?= $quality == $top_wp10_quality ? ' style="color: blue; font-weight: bold;"' : "" ?>>
                                    <td>Wp10 "<?= $quality ?>"</td>
                                    <td><?= number_format($prob, 4) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}
?>