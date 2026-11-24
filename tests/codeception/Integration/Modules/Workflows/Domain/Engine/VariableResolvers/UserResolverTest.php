<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\UserResolver;
use stdClass;
use Tests\Support\UnitTester;

class UserResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $resolver = new UserResolver(new stdClass());

        $this->assertEquals('user', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertEquals('23', $resolver->getValueAsString('ID'));
        $this->assertEquals('23', $resolver->getValueAsString('id'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyDoesNotExist(): void
    {
        $user = new stdClass();

        $resolver = new UserResolver($user);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_key'));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertTrue(isset($resolver->ID));
        $this->assertTrue(isset($resolver->id));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertFalse(isset($resolver->non_existent_key));
    }

    public function testGetReturnsValueWhenPropertyExists(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertEquals(23, $resolver->ID);
    }

    public function testGetReturnsNullWhenPropertyDoesNotExist(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertNull($resolver->non_existent_key);
    }

    public function testSetDoesNothing(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $resolver->ID = 24;

        $this->assertEquals(23, $resolver->ID);
    }

    public function testUnsetDoesNothing(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        unset($resolver->ID);

        $this->assertEquals(23, $resolver->ID);
    }

    public function testToStringReturnsCorrectValue(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertEquals('{"ID":23}', (string) $resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $user = new stdClass();
        $user->ID = 23;

        $resolver = new UserResolver($user);

        $this->assertEquals(['type' => 'user', 'value' => 23], $resolver->compact());
    }

    public function testGetVariableReturnsNullWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertNull($resolver->getVariable());
    }

    public function testGetTypeReturnsCorrectTypeWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertEquals('user', $resolver->getType());
    }

    public function testGetValueAsStringReturnsEmptyStringWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertEquals('', $resolver->getValueAsString('ID'));
        $this->assertEquals('', $resolver->getValueAsString('id'));
        $this->assertEquals('', $resolver->getValueAsString('user_login'));
        $this->assertEquals('', $resolver->getValueAsString('user_email'));
        $this->assertEquals('', $resolver->getValueAsString('roles'));
        $this->assertEquals('', $resolver->getValueAsString('caps'));
        $this->assertEquals('', $resolver->getValueAsString('display_name'));
        $this->assertEquals('', $resolver->getValueAsString('registered'));
    }

    public function testGetValueReturnsNullWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertEquals('', $resolver->getValue('ID'));
        $this->assertEquals('', $resolver->getValue('user_login'));
        $this->assertEquals('', $resolver->getValue('user_email'));
        $this->assertEquals('', $resolver->getValue('roles'));
        $this->assertEquals('', $resolver->getValue('caps'));
        $this->assertEquals('', $resolver->getValue('display_name'));
        $this->assertEquals('', $resolver->getValue('registered'));
    }

    public function testCompactReturnsArrayWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertEquals(['type' => 'user', 'value' => ''], $resolver->compact());
    }

    public function testIssetReturnsTrueWhenPropertyExistsAndUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertTrue(isset($resolver->ID));
        $this->assertTrue(isset($resolver->id));
        $this->assertTrue(isset($resolver->user_login));
        $this->assertTrue(isset($resolver->user_email));
        $this->assertTrue(isset($resolver->roles));
        $this->assertTrue(isset($resolver->caps));
        $this->assertTrue(isset($resolver->display_name));
        $this->assertTrue(isset($resolver->registered));
    }

    public function testGetReturnsEmptyStringWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $this->assertEquals('', $resolver->ID);
        $this->assertEquals('', $resolver->user_login);
        $this->assertEquals('', $resolver->user_email);
        $this->assertEquals('', $resolver->roles);
        $this->assertEquals('', $resolver->caps);
        $this->assertEquals('', $resolver->display_name);
        $this->assertEquals('', $resolver->registered);
    }

    public function testSetValueDoesNothingWhenUserIsNull(): void
    {
        $resolver = new UserResolver(null);

        $resolver->setValue('ID', 23);

        $this->assertEquals('', $resolver->ID);
    }

    public function testSetValueUpdatesProperty(): void
    {
        $user = new stdClass();
        $user->ID = 23;
        $user->user_login = 'testuser';

        $resolver = new UserResolver($user);

        $resolver->setValue('ID', 24);
        $resolver->setValue('user_login', 'newuser');

        $this->assertEquals(24, $resolver->ID);
        $this->assertEquals('newuser', $resolver->user_login);
    }
}
