<?php
/**
 * Created by PhpStorm.
 * User: Alicia
 * Date: 2019-05-09
 * Time: 17:10
 */

namespace App\Normalizer;

abstract class AbstractNormalizer implements NormalizerInterface
{
    protected $exceptionTypes;

    public function __construct(array $exceptionTypes)
    {
        $this->exceptionTypes = $exceptionTypes;

    }

    public function supports(\Exception $exception)
    {
        return in_array(get_class($exception), $this->exceptionTypes);
    }
}