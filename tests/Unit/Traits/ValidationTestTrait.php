<?php

namespace App\Tests\Unit\Traits;

trait ValidationTestTrait
{

    public const MIN_LENGTH_ERROR_CODE = '9ff3fdc4-b214-49db-8718-39c315e33d45';
    public const NOT_BLANK_ERROR_CODE = 'c1051bb4-d103-4f74-8988-acbcafc7fdc3';

    /**
     * Assert that the validation errors of an object match the expected errors.
     *
     * @param object $object
     * @param array $expectedErrors ['title' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3', 'content' => 'Cette valeur est obligatoire']
     * @return void
     */
    protected function assertValidationErrors(object $object, array $expectedErrors): void
    {
        $errors = $this->validator->validate($object);

        $errorSearch = $errors->findByCodes($expectedErrors['code']);

        $this->assertNotEmpty($errorSearch, 'Expected validation error not found');
        $this->assertEquals($expectedErrors['property'], $errorSearch[0]->getPropertyPath(), 'Validation errors do not match expected errors');
    }
}
