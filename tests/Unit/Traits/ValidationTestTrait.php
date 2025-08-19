<?php

namespace App\Tests\Unit\Traits;

trait ValidationTestTrait
{
    /**
     * Undocumented function
     *
     * @param object $object
     * @param array $expectedErrors ['title' => 'Ce titre est déjà utilisé', 'content' => 'Cette valeur est obligatoire']
     * @return void
     */
    protected function assertValidationErrors(object $object, array $expectedErrors): void
    {
        $errors = $this->validator->validate($object);

        $messages = [];

        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }

        $this->assertEquals($expectedErrors, $messages, 'Validation errors do not match expected errors');
    }
}
