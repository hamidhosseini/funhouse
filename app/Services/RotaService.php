<?php
namespace App\Services;

use DateTime;
use App\Models\Rota;
use App\Models\Shift;
use App\Models\Staff;
use Illuminate\Support\Carbon;

class RotaService
{
    private $rota;

    public function __construct(int $rotaId)
    {
        try {
            $this->rota = Rota::find($rotaId);
        } catch (\Exception $e) {
            throw new \Exception($e);     
        }
    }
    
    /**
     * Calculate the single manning minutes
     *
     * @return array
     */
    public function calculateManningMinutes()
    {
        $allShiftByDay = $this->getAllShiftsForRota();

        $manningMunitesInaDay = [];
        foreach ($allShiftByDay as $shiftsInaDay) {
            $manningMunitesInaDay[] = $this->calculateManningMinutesForaDay($shiftsInaDay);
        }

        return $manningMunitesInaDay;
    }
    
    /**
     * Get all the shifts grouped by day in a rota
     *
     */
    private function getAllShiftsForRota()
    {
        $allShiftByDay = Shift::where('rota_id', $this->rota->id)->get()->sortBy('start_time')->groupBy(function ($shift) {
            return Carbon::parse($shift->start_time)->format('d');
        });

        return $allShiftByDay;
    }
    
    /**
     * Calculate single manning minutes for a single day
     *
     * @param $shiftsInaDay
     *
     * @return void
     */
    private function calculateManningMinutesForaDay($shiftsInaDay)
    {
        $allShiftsTimeInMinutes = $this->getTotalShiftsTimesInMinutes($shiftsInaDay);

        $previousShiftStartTime = null;
        $previousShiftEndTime = null;

        $overlappingHours = null;
        $overlappingMinutes = null;

        foreach ($shiftsInaDay as $shift) {
            if (!$previousShiftEndTime) {
                $previousShiftStartTime = $shift->start_time;
                $previousShiftEndTime = $shift->end_time;
                continue;
            }

            if ($shift->start_time < $previousShiftEndTime) {
                $startTime  = date('H:i', strtotime($shift->start_time));
                $endTime    = date('H:i', strtotime($previousShiftEndTime));

                $startTime = new DateTime($startTime);
                $endTime = new DateTime($endTime);
                $interval = $startTime->diff($endTime);
                $overlappingHours += $interval->format('%H');
                $overlappingMinutes += $interval->format('%I');
            }
            
            $previousShiftStartTime = $shift->start_time;
            $previousShiftEndTime = $shift->end_time;
        }

        $totalOverlappingMinutes = ($overlappingHours * 60) + $overlappingMinutes;

        $totalManningMinutes = $allShiftsTimeInMinutes - $totalOverlappingMinutes;

        $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');
        $dayOfTheWeek = date('w', strtotime($shiftsInaDay->first()->start_time));

        return [
            'totalManningMinutes' => $totalManningMinutes,
            'dayOfTheWeek' => $days[$dayOfTheWeek],
            'date' => date('Y-m-d', strtotime($shiftsInaDay->first()->start_time))
        ];
    }
    
    /**
     * Get the total minutes of shifts in a day without overlapping minutes.
     *
     * @param Shift $shift [explicite description]
     *
     */
    private function getTotalShiftsTimesInMinutes($shiftsInaDay)
    {
        $earliestShift = $shiftsInaDay->first();
        $latestshift = $shiftsInaDay->last();

        $start  = date('H:i', strtotime($earliestShift->start_time));
        $end    = date('H:i', strtotime($latestshift->end_time));;

        $start  = new Carbon($earliestShift->start_time);
        $end    = new Carbon($latestshift->end_time);
        $shiftHours = $start->diff($end)->format('%H');
        $shiftMinutes = $start->diff($end)->format('%I');
        

        return ($shiftHours * 60) + $shiftMinutes;
    }
}