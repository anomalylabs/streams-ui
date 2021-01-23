<?php

namespace Streams\Ui\Tests\Input;

use Tests\TestCase;
use Streams\Ui\Input\Select;
use Streams\Core\Support\Facades\Streams;

class SelectTest extends TestCase
{

    public function setUp(): void
    {
        $this->createApplication();

        Streams::load(base_path('vendor/streams/ui/tests/litmus.json'));
    }

    public function testInput()
    {
        $entry = Streams::repository('testing.litmus')->find('field_types');
        $input = $entry->stream()->fields->select->input();

        $this->assertInstanceOf(Select::class, $input);
    }

    public function testOptions()
    {
        $entry = Streams::repository('testing.litmus')->find('field_types');
        $field = $entry->stream()->fields->select;
        $input = $field->input();

        $this->assertEquals([
            'foo' => 'Foo',
            'bar' => 'Bar',
        ], $input->field->config['options']);
    }
}
