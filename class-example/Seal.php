<?php

class Seal {

    private $name;
    private $weight;
    private $location;
    private $isAlive;

    public function __construct($name, $weight, $location) {
        $this->name = $name;
        $this->weight = $weight;
        $this->location = $location;
        $this->isAlive = true;
    }

    public function getName() {
        return $this->name;
    }

    public function getWeight() {
        return $this->weight;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setLocation($location) {
        $this->location = $location;
    }

    public function isAlive() {
        return $this->isAlive;
    }

    public function beHunted() {
        if ($this->isAlive) {
            echo $this->name . " has been hunted!<br>";
            $this->isAlive = false;
            return $this->weight;
        } else {
            echo $this->name . " is already gone.<br>";
            return 0;
        }
    }

    public function swim() {
        if($this->isAlive){
            echo $this->name . " is swimming.<br>";
            $this->location = "in the water";
        } else {
            echo $this->name . " can't swim, it's not alive.<br>";
        }
    }

    public function displayStats() {
        echo "Seal Name: " . $this->name . "<br>";
        echo "Seal Weight: " . $this->weight . " kg<br>";
        echo "Seal Location: " . $this->location . "<br>";
        echo "Seal Alive: " . ($this->isAlive ? "Yes" : "No") . "<br>";
    }
}

?>
