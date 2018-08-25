<?php
/**
 * Created by PhpStorm.
 * User: dong
 * Date: 2018/8/25
 * Time: 23:18
 */
namespace Paymen\Job;

use Think\Log;
use think\queue\Job;

class AutoDf
{

    /**
     * Fire the job.
     * @param Job $job
     * @param $data
     * @return void
     */
    public function fire(Job $job, $data)
    {
        // TODO: Implement fire() method.
        if ($job->attempts() > 3) {
            Log::record('job次数超限：' . $data);
            $job->delete();
        } else {
            echo $data;
        }
    }

    public function failed($data){

        // ...任务达到最大重试次数后，失败了
        Log::record('job次数超限：' . $data);
    }

}