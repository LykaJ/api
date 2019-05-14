<?php
/**
 * Created by PhpStorm.
 * User: Alicia
 * Date: 2019-05-09
 * Time: 17:09
 */

namespace App\Normalizer;


interface NormalizerInterface
{
    public function normalize(\Exception $exception);

    public function supports(\Exception $exception);
}