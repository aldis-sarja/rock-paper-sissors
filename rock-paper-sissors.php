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
    private int $totalWins = 0;
    private int $wins = 0;
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

/*
$game = new Game();
$game->testMultiFight();
*/

class Tournament extends Game
{
    /**
     * @var Player[] $players
     */
    private array $players;

    /**
     * @var MatchScore[]
     */
    private array $allMatches = [];

    public function __construct(int $numberOfPlayers)
    {
        parent::__construct();

        for ($c = 1; $c <= $numberOfPlayers-1; $c++) {
            $this->players[] = new Player("CPU core-{$c}", true);
        }
        $this->players[] = new Player(readline("Enter your name: "));
    }

    public function match(Player $player1, Player $player2, int $minWins): Player
    {
        $player1->setWins(0);
        $player2->setWins(0);

        echo "~-=={$player1->getName()} VS {$player2->getName()}==-~\n";

//        $c = 1;
//        while (($c <= $rounds) || ($player1->getWins() === $player2->getWins())) {
        while ((($player1->getWins() < $minWins) && ($player2->getWins() < $minWins))
            || ($player1->getWins() === $player2->getWins())) {
            if (!$player1->isCPU()) {
                $player1->setSelection($this->elements[$this->chooseElement($player1)]);
            } else {
                $player1->setSelection($this->elements[array_rand($this->elements)]);
            }

            if (!$player2->isCPU()) {
                $player2->setSelection($this->elements[$this->chooseElement($player2)]);
            } else {
                $player2->setSelection($this->elements[array_rand($this->elements)]);
            }

            echo "{$player1->getName()} - {$player1->getSelection()->getName()}\n";
            echo "{$player2->getName()} - {$player2->getSelection()->getName()}\n";

            $winner = $this->fight1on1($player1, $player2);
            if ($winner !== null) {
                $winner->setWins($winner->getWins() + 1);
                $winner->setTotalWins($winner->getTotalWins() + 1);
                echo "{$winner->getName()} won\n";
            } else echo "It's tie!\n";
//            $c++;
        }
        return $player1->getWins() > $player2->getWins() ? $player1 : $player2;
    }

    public function run(): void
    {
        $playersQueue = $this->players;

        while (count($playersQueue) > 1) {
            $nextQueue = [];
            while (count($playersQueue) > 1) {
                $player1 = array_pop($playersQueue);
                $player2 =  array_pop($playersQueue);
                $winner = $this->match($player1, $player2, 2);

                echo "And finally {$winner->getName()} won\n";

                $nextQueue[] = $winner;
                $looser = $winner === $player1 ? $player2 : $player1;

                $this->allMatches[] = new MatchScore(
                    $winner, $winner->getWins(),
                    $looser, $looser->getWins()
                );
            }
            if (current($playersQueue)) {
                $nextQueue[] = current($playersQueue);
            }
            $playersQueue = $nextQueue;
        }

        echo "=============MATCHES============\n";
        foreach ($this->allMatches as $match) {
            echo "{$match->getWinner()->getName()}: {$match->getScore1()}\n";
            echo "{$match->getLooser()->getName()}: {$match->getScore2()}\n";
            echo PHP_EOL;
        }
        echo "And the winner is: {$winner->getName()} with totals wins: {$winner->getTotalWins()}\n";
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
