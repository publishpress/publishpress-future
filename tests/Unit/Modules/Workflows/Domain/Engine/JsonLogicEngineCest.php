<?php


namespace Tests\Unit\Modules\Workflows\Domain\Engine;

use PublishPress\Future\Modules\Workflows\Domain\Engine\JsonLogicEngine;
use Tests\Support\UnitTester;

class JsonLogicEngineCest
{
    public function _before(UnitTester $I)
    {
    }

    public function testOperationStartsWith(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationStartsWith('hello', 'h'));
        $I->assertTrue($engine->operationStartsWith('hello', 'he'));
        $I->assertTrue($engine->operationStartsWith('hello', 'hello'));
        $I->assertTrue($engine->operationStartsWith('12566', '1'));
        $I->assertTrue($engine->operationStartsWith('12566', '12'));
        $I->assertFalse($engine->operationStartsWith('hello', 'eh'));
        $I->assertFalse($engine->operationStartsWith('hello', 'el'));
        $I->assertFalse($engine->operationStartsWith('hello', 'll'));
        $I->assertFalse($engine->operationStartsWith('hello', 'lo'));
        $I->assertFalse($engine->operationStartsWith('hello', 'o'));
    }

    public function testOperationEndsWith(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationEndsWith('hello', 'o'));
        $I->assertTrue($engine->operationEndsWith('hello', 'lo'));
        $I->assertTrue($engine->operationEndsWith('hello', 'llo'));
        $I->assertTrue($engine->operationEndsWith('hello', 'ello'));
        $I->assertTrue($engine->operationEndsWith('hello', 'hello'));
        $I->assertFalse($engine->operationEndsWith('hello', 'eh'));
        $I->assertFalse($engine->operationEndsWith('hello', 'el'));
        $I->assertFalse($engine->operationEndsWith('hello', 'll'));
    }

    public function testOperationContains(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationContains('hello', 'o'));
        $I->assertTrue($engine->operationContains('hello', 'lo'));
        $I->assertTrue($engine->operationContains('hello', 'llo'));
        $I->assertTrue($engine->operationContains('hello', 'ello'));
        $I->assertTrue($engine->operationContains('hello', 'hello'));
    }

    public function testOperationDoesNotContain(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationDoesNotContain('hello', 'k'));
        $I->assertTrue($engine->operationDoesNotContain('hello', '9'));
        $I->assertTrue($engine->operationDoesNotContain('hello', '12'));
        $I->assertFalse($engine->operationDoesNotContain('hello', 'o'));
        $I->assertFalse($engine->operationDoesNotContain('hello', 'lo'));
        $I->assertFalse($engine->operationDoesNotContain('hello', 'llo'));
        $I->assertFalse($engine->operationDoesNotContain('hello', 'ello'));
        $I->assertFalse($engine->operationDoesNotContain('hello', 'hello'));
    }

    public function testOperationDoesNotBeginWith(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationDoesNotBeginWith('hello', 'k'));
        $I->assertTrue($engine->operationDoesNotBeginWith('hello', '9'));
        $I->assertTrue($engine->operationDoesNotBeginWith('hello', '12'));
        $I->assertFalse($engine->operationDoesNotBeginWith('hello', 'h'));
        $I->assertFalse($engine->operationDoesNotBeginWith('hello', 'he'));
    }

    public function testOperationDoesNotEndWith(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationDoesNotEndWith('hello', 'k'));
        $I->assertTrue($engine->operationDoesNotEndWith('hello', '9'));
        $I->assertTrue($engine->operationDoesNotEndWith('hello', '12'));
        $I->assertFalse($engine->operationDoesNotEndWith('hello', 'o'));
    }

    public function testOperationNull(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationNull(null));
        $I->assertFalse($engine->operationNull('hello'));
    }

    public function testOperationNotNull(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationNotNull('hello'));
        $I->assertFalse($engine->operationNotNull(null));
    }

    public function testOperationIn(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationIn('hello', ['hello', 'world']));
        $I->assertTrue($engine->operationIn('world', ['hello', 'world']));
        $I->assertFalse($engine->operationIn('hello', ['world']));
        $I->assertFalse($engine->operationIn('hello', []));

        $I->assertTrue($engine->operationIn('hello', 'hello world'));
        $I->assertTrue($engine->operationIn('hello', 'hello,world'));
        $I->assertTrue($engine->operationIn('hello', 'hello, world'));
    }

    public function testOperationNotIn(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationNotIn('hello', ['world']));
        $I->assertTrue($engine->operationNotIn('world', ['hello']));
        $I->assertTrue($engine->operationNotIn('hello', []));
        $I->assertFalse($engine->operationNotIn('hello', ['hello', 'world']));
    }

    public function testOperationBetween(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationBetween(5, 1, 10));
        $I->assertTrue($engine->operationBetween(1, 1, 10));
        $I->assertTrue($engine->operationBetween(10, 1, 10));
        $I->assertFalse($engine->operationBetween(0, 1, 10));
        $I->assertFalse($engine->operationBetween(11, 1, 10));
    }

    public function testOperationNotBetween(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationNotBetween(0, 1, 10));
        $I->assertTrue($engine->operationNotBetween(11, 1, 10));
        $I->assertFalse($engine->operationNotBetween(5, 1, 10));
        $I->assertFalse($engine->operationNotBetween(1, 1, 10));
        $I->assertFalse($engine->operationNotBetween(10, 1, 10));
    }

    public function testApply(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->apply(
            [
                'and' => [
                    'in' => ['Hello', ['var' => 'onPostUpdated1.postBefore.post_title']],
                    'in' => ['Hey world', ['var' => 'onPostUpdated1.postAfter.post_title']],
                ]
            ],
            [
                'onPostUpdated1' => [
                    'postBefore' => ['post_title' => 'Hello'],
                    'postAfter' => ['post_title' => 'Hey world'],
                ]
            ]
        ));
    }

    public function testOperationIsEmpty(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationIsEmpty(''));
        $I->assertTrue($engine->operationIsEmpty(null));
        $I->assertTrue($engine->operationIsEmpty(0));
        $I->assertTrue($engine->operationIsEmpty([]));

        $I->assertFalse($engine->operationIsEmpty(1));
        $I->assertFalse($engine->operationIsEmpty('hello'));
        $I->assertFalse($engine->operationIsEmpty(['hello']));
        $I->assertFalse($engine->operationIsEmpty([0]));
        $I->assertFalse($engine->operationIsEmpty(['hello' => 'world']));
        $I->assertFalse($engine->operationIsEmpty(new \stdClass()));
    }

    public function testOperationIsNotEmpty(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->operationIsNotEmpty(1));
        $I->assertTrue($engine->operationIsNotEmpty('hello'));
        $I->assertTrue($engine->operationIsNotEmpty(['hello']));
        $I->assertTrue($engine->operationIsNotEmpty([0]));
        $I->assertTrue($engine->operationIsNotEmpty(['hello' => 'world']));
        $I->assertTrue($engine->operationIsNotEmpty(new \stdClass()));

        $I->assertFalse($engine->operationIsNotEmpty(''));
        $I->assertFalse($engine->operationIsNotEmpty(null));
        $I->assertFalse($engine->operationIsNotEmpty(0));
        $I->assertFalse($engine->operationIsNotEmpty([]));
    }

    public function testApplyIsEmpty(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->apply(['isEmpty' => [['var' => 'test']]], ['test' => '']));
        $I->assertTrue($engine->apply(['isEmpty' => [['var' => 'test']]], ['test' => null]));
        $I->assertTrue($engine->apply(['isEmpty' => [['var' => 'test']]], ['test' => 0]));
        $I->assertTrue($engine->apply(['isEmpty' => [['var' => 'test']]], ['test' => []]));
        $I->assertFalse($engine->apply(['isEmpty' => [['var' => 'test']]], ['test' => 'hello']));
        $I->assertFalse($engine->apply(['isEmpty' => [['var' => 'test']]], ['test' => ['hello']]));
    }

    public function testApplyIsNotEmpty(UnitTester $I)
    {
        $engine = new JsonLogicEngine();

        $I->assertTrue($engine->apply(['isNotEmpty' => [['var' => 'test']]], ['test' => 'hello']));
        $I->assertTrue($engine->apply(['isNotEmpty' => [['var' => 'test']]], ['test' => ['hello']]));
        $I->assertFalse($engine->apply(['isNotEmpty' => [['var' => 'test']]], ['test' => '']));
        $I->assertFalse($engine->apply(['isNotEmpty' => [['var' => 'test']]], ['test' => null]));
    }
}
