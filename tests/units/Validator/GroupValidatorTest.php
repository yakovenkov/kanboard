<?php

require_once __DIR__.'/../Base.php';

use Kanboard\Validator\GroupValidator;

class GroupValidatorTest extends Base
{
    public function testValidateCreation()
    {
        $validator = new GroupValidator($this->container);

        $result = $validator->validateCreation(array('name' => 'Test'));
        $this->assertTrue($result[0]);

        $result = $validator->validateCreation(array('name' => ''));
        $this->assertFalse($result[0]);
    }

    public function testValidateModification()
    {
        $validator = new GroupValidator($this->container);

        $result = $validator->validateModification(array('name' => 'Test', 'id' => 1));
        $this->assertTrue($result[0]);

        $result = $validator->validateModification(array('name' => 'Test'));
        $this->assertFalse($result[0]);
    }
}
