<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/10/30
 * Time: 下午2:56
 */

namespace App\Transformers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class SevenDaysStatisticsTransformer extends TransformerAbstract
{
    /**
     * Transform the  SevenDaysStatistics entity.
     * @param Collection $collection
     * @return array
     */
    public function transform(Collection $collection)
    {
        $statistics = [
            'last_week' => [],
            'this_week' => []
        ];
        $collection->map(function ($item) use(&$statistics){
            $item['paid_time'] = $item['paid_time'] == 0 ? 7 : $item['paid_time'];
            if($this->isLastWeek($item['paid_at'])) {
                while (count($statistics['last_week']) < $item['paid_time']) {
                    array_push($statistics['last_week'], 0);
                }
                $statistics['last_week'][$item['paid_time'] - 1] = $item['count'];
            }else{
                while (count($statistics['this_week'])  < $item['paid_time']) {
                    array_push($statistics['this_week'], 0);
                }
                $statistics['this_week'][$item['paid_time'] - 1] = $item['count'];
            }
        });
        if(count($statistics['last_week']) < 7) {
            while (count($statistics['last_week'])  < 7) {
                array_push($statistics['last_week'], 0);
            }

        }
        $day = Carbon::now(config('app.timezone'))->dayOfWeekIso;
        if(count($statistics['this_week']) < $day) {
            for($i = 0; $i < $day; $i ++) {
                array_push($statistics['this_week'], 0);
            }
        }
        Log::info('statistics', $statistics);
        return $statistics;
    }

    /**
     *
     * @param Carbon $time
     * @return bool
     */
    private function isLastWeek(Carbon $time)
    {
        $end = Carbon::now(config('app.timezone'));
        $start = $end->copy()->startOfWeek();
        if($start->timestamp > $time->timestamp) {
            return true;
        }else{
            return false;
        }
    }
}