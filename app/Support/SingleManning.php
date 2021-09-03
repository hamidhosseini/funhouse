<?php
namespace App\Support;

class SingleManning
{
    public $singleManningInMinutes;
    public $dayOfTheWeek;
    public $date; 

    public function __construct(int $singleManningInMinutes, string $dayOfTheWeek, string $date)
    {
        $this->singleManningInMinutes = $singleManningInMinutes;
        $this->dayOfTheWeek = $dayOfTheWeek;
        $this->date = $date;
    }
}