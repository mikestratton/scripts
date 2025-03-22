<?php

class PolarBear {

    private $name;
    private $age;
    private $weight;
    private $location;
    private $isHungry;

    public function __construct($name, $age, $weight, $location) {
        $this->name = $name;
        $this->age = $age;
        $this->weight = $weight;
        $this->location = $location;
        $this->isHungry = true;
    }

    public function getName() {
        return $this->name;
    }

    public function getAge() {
        return $this->age;
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

    public function isHungry() {
        return $this->isHungry;
    }

    public function hunt(Seal $seal) {
        if ($this->isHungry && $seal->isAlive()) {
            $sealWeight = $seal->beHunted();
            if ($sealWeight > 0){
                echo $this->name . " successfully hunted " . $seal->getName() . "!<br>";
                $this->isHungry = false;
                $this->weight += $sealWeight * 0.8;
            } else {
                echo $this->name . " failed to hunt " . $seal->getName() . ".<br>";
            }
        } else if (!$seal->isAlive()){
            echo $this->name . " can't hunt a dead seal.<br>";
        } else {
            echo $this->name . " is not hungry right now.<br>";
        }
    }

    public function swim() {
        echo $this->name . " is swimming in the icy water.<br>";
        $this->weight -= 5;
    }

    public function sleep() {
        echo $this->name . " is sleeping.<br>";
    }

    public function displayStats() {
        echo "Name: " . $this->name . "<br>";
        echo "Age: " . $this->age . " years<br>";
        echo "Weight: " . $this->weight . " kg<br>";
        echo "Location: " . $this->location . "<br>";
        echo "Hungry: " . ($this->isHungry ? "Yes" : "No") . "<br>";
    }
}

?>