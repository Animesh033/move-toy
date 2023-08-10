<?php
class MoveToy
{
    public $iTableX;
    public $iTableY;
    public $aDir;
    public $sRobotDir;
    public $aCommandSplit;
    public $iRobotX;
    public $iRobotY;
    public $bRobotPlaced;
    public $aTokens;
    public $aCommands;

    public function __construct($aCommands)
    {
        $this->aCommands = $aCommands;
        $this->iTableX = 5;
        $this->iTableY = 5;
        $this->aDir = ["NORTH", "EAST", "SOUTH", "WEST"];
        $this->sRobotDir = 0;
        $this->aCommandSplit = [];
        $this->iRobotX = 0;
        $this->iRobotY = 0;
        $this->bRobotPlaced = false;
        $this->aTokens = [];
        $this->robot();
    }

    function robot()
    {

        $iLen = count($this->aCommands);
        for ($iLoop = 0; $iLoop < $iLen; $iLoop++) {
            $this->aCommandSplit = explode(" ", strtoupper($this->aCommands[$iLoop]));
            if (count($this->aCommandSplit) == 2) {
                $this->aCommandSplit[1] = explode(",", $this->aCommandSplit[1]);
            } else {
                $this->aCommandSplit[1] = [];
            }
            array_push($this->aTokens, $this->aCommandSplit);
        }
        $this->startMoving();
    }

    function invalidCommand($item)
    {
        $invalid = join(" ", $item);
        echo ">> INVALID COMMAND: '" . $invalid . "' IGNORED";
    }

    function isBelowZero($val)
    {
        return ($val < 0);
    }

    function isBeyondTableLimit($val, $limit)
    {
        return (int)$val >= $limit;
    }

    function isPosInteger($str)
    {
        $n = floor((int)$str);
        return $n !== INF && (string)($n) === $str && $n >= 0;
    }

    function isDirection($str)
    {
        return in_array($str, $this->aDir);
    }

    function whichDirection($str)
    {
        return array_search($str, $this->aDir);
    }

    function checkPlaceParams($arr)
    {
        return (
            (!empty($arr)) &&
            (count($arr) == 3) &&
            $this->isPosInteger($arr[0]) &&
            $this->isPosInteger($arr[1]) &&
            (gettype($arr[2]) == "string") &&
            $this->isDirection($arr[2]) &&
            !$this->isBelowZero($arr[0]) &&
            !$this->isBelowZero($arr[1]) &&
            !$this->isBeyondTableLimit($arr[0], $this->iTableX) &&
            !$this->isBeyondTableLimit($arr[1], $this->iTableY)
        );
    }

    function place($item)
    {
        if ($this->checkPlaceParams($item[1])) {
            $this->iRobotX = +$item[1][0];
            $this->iRobotY = +$item[1][1];
            $this->sRobotDir = $this->whichDirection($item[1][2]);
            $this->bRobotPlaced = true;
        }
    }

    function rotate($turn)
    {
        $newDir = ($this->sRobotDir + (($turn == "LEFT") ? 3 : 1)) % 4;
        $this->sRobotDir = $newDir;
    }

    function canMove()
    {
        switch ($this->sRobotDir) {
            case 0:
                return (!$this->isBeyondTableLimit($this->iRobotY + 1, $this->iTableY));
                break;
            case 1:
                return (!$this->isBeyondTableLimit($this->iRobotX + 1, $this->iTableX));
                break;
            case 2:
                return (!$this->isBelowZero($this->iRobotY - 1));
                break;
            case 3:
                return (!$this->isBelowZero($this->iRobotX - 1));
                break;
        }
    }

    function move()
    {
        if ($this->sRobotDir % 2 == 1) {
            if ($this->sRobotDir == 1) {
                $this->iRobotX++;
            } else {
                $this->iRobotX--;
            }
        } else {
            if ($this->sRobotDir == 0) {
                $this->iRobotY++;
            } else {
                $this->iRobotY--;
            }
        }
    }

    function report()
    {
        echo join(",", [$this->iRobotX, $this->iRobotY, $this->aDir[$this->sRobotDir]]);
    }

    function startMoving()
    {
        foreach ($this->aTokens as $index => $item) {
            switch ($item[0]) {
                case "PLACE":
                    $this->place($item);
                    break;
                case "LEFT":
                case "RIGHT":
                    $this->bRobotPlaced ? $this->rotate($item[0]) : $this->invalidCommand($item);
                    break;
                case "MOVE":
                    ($this->bRobotPlaced && $this->canMove()) ? $this->move() : $this->invalidCommand($item);
                    break;
                case "REPORT":
                    $this->bRobotPlaced ? $this->report() : $this->invalidCommand($item);
                    break;
                default:
                    $this->invalidCommand($item);
                    break;
            }
        }
    }
}

$robot = new MoveToy(["PLACE 0,0,NORTH", "MOVE", "REPORT"]); // 0,1,NORTH
// $robot = new MoveToy(["PLACE 0,0,NORTH", "LEFT", "REPORT"]); // 0,0,WEST
// $robot = new MoveToy(["PLACE 1,2,EAST", "MOVE", "MOVE", "LEFT", "MOVE", "REPORT"]); // 3,3,NORTH

// $robot = new MoveToy(["PLACE 1,2,NORTH", "MOVE", "LEFT", "MOVE", "REPORT"]); // 0,3,WEST
// $robot = new MoveToy(["MOVE", "PLACE 0,3,WEST", "MOVE", "RIGHT", "MOVE", "MOVE", "REPORT"]); // INVALID MOVE
// $robot = new MoveToy(["PLACE 2,2,NORTH", "KEFT", "NOVE", "MOVE", "REPORT"]); // INVALID MOVE
