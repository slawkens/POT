<?php

/**
 * @ignore
 * @package examples
 * @author Wrzasq <wrzasq@gmail.com>
 * @copyright 2007 (C) by Wrzasq
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License, Version 3
 */

// to not repeat all that stuff
include('quickstart.php');

// loads guild
$guild = $ots->createObject('Guild');
$guild->load(1);

$color = '#FFFFCC';

echo '<h1>Members of ', htmlspecialchars( $guild->getName() ), '</h1>';

?>
<table>
    <thead>
        <tr>
            <th>Rank</th>
            <th>Members</th>
        </tr>
    </thead>
    <tbody>
<?php

// lists members of all ranks
foreach( $guild->getGuildRanks() as $guildRank)
{
    // display rank in first row
    $first = true;
    // switches rank rows color
    $color = $color == '#FFFFCC' ? '#FFCCFF' : '#FFFFCC';

    // list members of this rank
    foreach( $guildRank->getPlayers() as $player)
    {
        echo '<tr style="background-color: ', $color, '">
    <td>', $first ? htmlspecialchars( $guildRank->getName() ) : '', '</td>
    <td>', $player->getName(), '</td>
</tr>';
        $first = false;
    }
}

?>
    </tbody>
</table>