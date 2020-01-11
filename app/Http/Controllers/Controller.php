<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __call($name, $arguments)
    {
        $request = $arguments[0];

        $this->checkValidation($name, $request);
        $this->checkPolicy($name, $arguments);

        return call_user_func_array([$this, $name], $arguments);
    }

    private function checkValidation($name, $request)
    {
        $class = 'App\\Validations\\'
            . $this->removeNamespaceOfClassPath(static::class)
            . 'Validation';

        if (!class_exists($class)) {
            throw new \Exception(
                $class . ' Validation class is not exists!'
            );
        }

        $validation = new $class;

        if (!method_exists($validation, $name)) {
            throw new \Exception(
                $name . ' method is not implemented in the validation class ' . $class
            );
        }
        $this->validate($request, $validation->$name());
    }

    private function checkPolicy($name, $arguments)
    {
        $class = 'App\\Policies\\'
            . $this->removeNamespaceOfClassPath(static::class)
            . 'Policy';

        if (!class_exists($class)) {
            throw new \Exception(
                $class . ' Policy class is not exists!'
            );
        }

        $policy = new $class;

        if (!method_exists($policy, $name)) {
            throw new \Exception(
                $name . ' method is not implemented in the policy class ' . $class
            );
        }

        if (!$policy->$name($arguments)) {
            abort(403, trans('errors.access_denied'));
        }
    }

    private function removeNamespaceOfClassPath(string $classPath)
    {
        $buckets = explode('\\', $classPath);

        if (is_array($buckets) && count($buckets) > 1) {
            return $buckets[count($buckets) - 1];
        }

        return $classPath;
    }
}
