<?php

class Element
{
    private string $name;
    /**
     * @var Element[] $winOver
     */
    private array $winOver;

    public function __construct(string $name)
    {
        $this->name = $name;

    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWinOver(): array
    {
        return $this->winOver;
    }

    public function addWeakerElement(Element $element): void
    {
        $this->winOver[] = $element;
    }

    public function addWeakerList(array $elements): void
    {
        foreach ($elements as $element) {
            $this->addWeakerElement($element);
        }
    }

    public function whoIsStronger(Element $other): int
    {
        if (in_array($other, $this->getWinOver())) {
            return 1;
        }
        if (in_array($this, $other->getWinOver())) {
            return -1;
        }
        return 0;
    }
}

class Player
{
    private string $name;
    private Element $selection;
    //private int $totalWins = 0;
    //private int $wins = 0;
    private bool $isCPU;

    public function __construct(string $name, bool $isCPU = false)
    {
        $this->name = $name;
        $this->isCPU = $isCPU;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSelection(): Element
    {
        return $this->selection;
    }

    public function setSelection(Element $selection): void
    {
        $this->selection = $selection;
    }

    public function isCPU(): bool
    {
        return $this->isCPU;
    }

    public function setWins(int $wins): void
    {
        $this->wins = $wins;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function setTotalWins(int $totalWins): void
    {
        $this->totalWins = $totalWins;
    }

    public function getTotalWins(): int
    {
        return $this->totalWins;
    }
}

class Game
{
    /**
     * @var Element[] $elements
     */
    protected array $elements;
    protected int $elementPadding = 0;

    public function __construct()
    {
        $this->setup();
    }

    public function setup(): void
    {
        $this->elements = [
            $rock = new Element('rock'),
            $paper = new Element('paper'),
            $scissors = new Element('scissors'),
        ];
        $rock->addWeakerList([$scissors]);
        $paper->addWeakerList([$rock]);
        $scissors->addWeakerList([$paper]);

        /**
         * @var Element $element
         */
        foreach ($this->elements as $element) {
            $len = strlen($element->getName());
            if ($len > $this->elementPadding) {
                $this->elementPadding = $len;
            }
        }
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function getElementPadding(): int
    {
        return $this->elementPadding;
    }

    public function run(): void
    {
        $player1 = new Player(readline("Enter name for player 1: "));
        $player2 = new Player(readline("Enter name for player 2: "));
        echo "What is your choice?\n";
        for ($i = 0; $i < count($this->elements); $i++) {
            echo "{$i}: {$this->elements[$i]->getName()}\n";
        }

        $player1->setSelection($this->elements[(int)readline("For Player {$player1->getName()}: ")]);
        $player2->setSelection($this->elements[(int)readline("For Player {$player2->getName()}: ")]);

        switch ($player1->getSelection()->whoIsStronger($player2->getSelection())) {
            case 1:
                echo "{$player1->getName()} won!\n";
                break;
            case -1:
                echo "{$player2->getName()} won!\n";
                break;
            default:
                echo "Game is tie!\n";
        }
    }

    /**
     * @param Player[] $players
     */
    function multiFight(array $players): ?Player
    {
        $notLosers = $players;

        foreach ($notLosers as $idx => $player1) {
            foreach ($players as $player2) {
                if ($player1->getSelection()->whoIsStronger($player2->getSelection()) < 0) {
                    unset($notLosers[$idx]);
                    break;
                }
            }
        }
        if (count($notLosers) !== 1) {
            return null;
        }
        return current($notLosers);
    }

    public function testMultiFight(): void
    {
        $players = [
            $player1 = new Player("Player I"),
            $player2 = new Player("Player II"),
            $player3 = new Player("Player III"),
        ];

        for ($i = 0; $i < 5; $i++) {
            echo "============================\n";
            foreach ($players as $player) {
                $player->setSelection($this->elements[array_rand($this->elements)]);
                echo "{$player->getName()} - {$player->getSelection()->getName()}\n";
            }

            $winner = $this->multiFight($players);
            if ($winner === null) {
                echo "Nobody wins\n";
            } else {
                echo "{$winner->getName()} won!\n";
            }
            echo PHP_EOL;
        }
    }

    public function fight1on1(Player $player1, Player $player2): ?Player
    {
        if ($player1->getSelection() === $player2->getSelection()) {
            return null;
        }
        if ($player1->getSelection()->whoIsStronger($player2->getSelection()) === 1) {
            return $player1;
        }
        return $player2;
    }

    public function chooseElement(Player $player): int
    {
        for ($i = 0; $i < count($this->elements); $i++) {
            echo "{$i}: {$this->elements[$i]->getName()}\n";
        }

        return (int)readline("{$player->getName()}, what is your choice?: ");
    }
}

class GameSet
{
    private Player $player1;
    private Player $player2;
    private Player $winner;
    private Player $looser;
    private PlayerRecord $playerRecord1;
    private PlayerRecord $playerRecord2;
    private PlayerRecord $winnerRecord;
    private PlayerRecord $looserRecord;

    public function __construct(Player $player1, Player $player2)
    {
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->playerRecord1 = new PlayerRecord($player1);
        $this->playerRecord2 = new PlayerRecord($player2);
    }

    public function getWinnerRecord(): PlayerRecord
    {
        return $this->winnerRecord;
    }

    public function getLooserRecord(): PlayerRecord
    {
        return $this->looserRecord;
    }

    public function getWinner(): Player
    {
        return $this->winner;
    }

    public function getLooser(): Player
    {
        return $this->looser;
    }

    public function play(int $minWins): Player
    {
        $game = new Game();
        $elements = $game->getElements();

        $player1 = $this->player1;
        $player2 = $this->player2;
        $playerScore1 = 0;
        $playerScore2 = 0;
        $winner = null;

        while (($playerScore1 < $minWins) && ($playerScore2 < $minWins)) {
            if (!$player1->isCPU()) {
                $player1->setSelection($elements[$game->chooseElement($player1)]);
            } else {
                $player1->setSelection($elements[array_rand($elements)]);
            }
            $this->playerRecord1->addElement($player1->getSelection());

            if (!$player2->isCPU()) {
                $player2->setSelection($elements[$game->chooseElement($player2)]);
            } else {
                $player2->setSelection($elements[array_rand($elements)]);
            }
            $this->playerRecord2->addElement($player2->getSelection());

            $winner = $game->fight1on1($player1, $player2);

            if ($winner === $player1) {
                $playerScore1++;
            }
            if ($winner === $player2) {
                $playerScore2++;
            }
            $this->playerRecord1->addScore($playerScore1);
            $this->playerRecord2->addScore($playerScore2);

            if ($winner !== null) {
                echo "{$winner->getName()} won\n";
            } else echo "It's tie!\n";
        }
        if ($playerScore1 > $playerScore2) {
            $this->winner = $player1;
            $this->winnerRecord = $this->playerRecord1;
            $this->looser = $player2;
            $this->looserRecord = $this->playerRecord2;
        } else {
            $this->winner = $player2;
            $this->winnerRecord = $this->playerRecord2;
            $this->looser = $player1;
            $this->looserRecord = $this->playerRecord1;
        }

        return $winner;
    }
}

/*
$game = new Game();
$game->testMultiFight();
*/

class Tournament //extends Game
{
    /**
     * @var Player[] $players
     */
    private array $players;
    private int $namePadding = 0;

    /**
     * @var MatchScore[]
     */
//    private array $allMatches = [];
    /**
     * @var GameSet[]
     */
    private array $allGames = [];

    public function __construct(int $numberOfPlayers)
    {
        for ($c = 1; $c <= $numberOfPlayers - 1; $c++) {
            $this->players[] = new Player("CPU core-{$c}", true);
        }
        $this->players[] = new Player(readline("Enter your name: "));

        foreach ($this->players as $player) {
            $len = strlen($player->getName());
            if ($len > $this->namePadding) {
                $this->namePadding = $len;
            }
        }
    }

    public function run(): void
    {
        $playersQueue = $this->players;

        while (count($playersQueue) > 1) {
            $nextQueue = [];
            while (count($playersQueue) > 1) {
                $player1 = array_pop($playersQueue);
                $player2 = array_pop($playersQueue);

                $game = new GameSet($player1, $player2);
                $this->allGames[] = $game;

                $winner = $game->play(2);

                $nextQueue[] = $winner;
            }

            if (current($playersQueue)) {
                $nextQueue[] = current($playersQueue);
            }
            $playersQueue = $nextQueue;
        }
        $this->displayScores($winner);
    }

    public function displayScores(Player $win): void
    {
        $elementPadding = (new Game)->getElementPadding();
        echo "=============MATCHES============\n";
        /**
         * @var GameSet $game
         * @var PlayerRecord $winner
         * @var PlayerRecord $looser
         */
        foreach ($this->allGames as $game) {
            $winner = $game->getWinnerRecord();
            $looser = $game->getLooserRecord();
            echo '~--===' . $winner->getPlayer()->getName()
                . ' VS ' . $looser->getPlayer()->getName() . "===--~\n";

            echo str_pad($winner->getPlayer()->getName(), $this->namePadding) . ' :';

            $winnerElementTable = $winner->getElementsTable();
            $winnerScoreTable = $winner->getScoreTable();
            for ($i = 0; $i < count($winnerElementTable); $i++) {
                echo ' ';
                echo str_pad($winnerElementTable[$i]->getName(), $elementPadding, ' ', STR_PAD_LEFT);
                echo ' s: ';
                echo $winnerScoreTable[$i];
            }
            echo " - WIN\n";

            echo str_pad($looser->getPlayer()->getName(), $this->namePadding) . ' :';

            $looserElementTable = $looser->getElementsTable();
            $looserScoreTable = $looser->getScoreTable();
            for ($i = 0; $i < count($looserElementTable); $i++) {
                echo ' ';
                echo str_pad($looserElementTable[$i]->getName(), $elementPadding, ' ', STR_PAD_LEFT);
                echo ' s: ';
                echo $looserScoreTable[$i];
            }
            echo " - LOSE\n";
            echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
        }
        echo PHP_EOL;
        echo "And the winner is: {$win->getName()}\n";
    }
}

class PlayerRecord
{
    private Player $player;
    private int $wins = 0;
    private array $scoreTable = [];
    private array $elementsTable = [];

    public function __construct(Player $player)
    {

        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function getScoreTable(): array
    {
        return $this->scoreTable;
    }

    public function addScore(int $score): void
    {
        $this->scoreTable[] = $score;
    }

    public function getElementsTable(): array
    {
        return $this->elementsTable;
    }

    public function addElement(Element $element): void
    {
        $this->elementsTable[] = $element;
    }
}

class MatchScore
{
    private Player $winner;
    private int $score1 = 0;
    private Player $looser;
    private int $score2 = 0;

    public function __construct(Player $winner, int $score1, Player $looser, int $score2)
    {

        $this->winner = $winner;
        $this->score1 = $score1;
        $this->looser = $looser;
        $this->score2 = $score2;
    }

    public function getWinner(): Player
    {
        return $this->winner;
    }

    public function getScore1(): int
    {
        return $this->score1;
    }

    public function getLooser(): Player
    {
        return $this->looser;
    }

    public function getScore2(): int
    {
        return $this->score2;
    }
}

$tournament = new Tournament(8);
$tournament->run();
