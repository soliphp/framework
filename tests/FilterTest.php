<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Filter;

class FilterTest extends TestCase
{
    public function testSanitize()
    {
        $filter = new Filter();

        $result = $filter->sanitize("!100a019", "int");
        $this->assertEquals("100019", $result);

        $result = $filter->sanitize("100a019", "int!");
        $this->assertEquals(100, $result);

        $result = $filter->sanitize("-100a019", "absint");
        // int(100)
        $this->assertEquals(100, $result);

        $result = $filter->sanitize("hello<<", "string");
        $this->assertEquals("hello", $result);

        $result = $filter->sanitize("!100a019.01a", "float");
        $this->assertEquals("100019.01", $result);

        $result = $filter->sanitize("100a019.01a", "float!");
        // double(100)
        $this->assertEquals(100.00, $result);

        $result = $filter->sanitize("100a019.01a#!foo", "alphanum");
        $this->assertEquals("100a01901afoo", $result);

        $result = $filter->sanitize("  foo#!bar\t", "trim");
        $this->assertEquals("foo#!bar", $result);

        $text = '<p>Test paragraph.</p><!-- Comment --> <a href="#fragment">Other text</a>';
        $result = $filter->sanitize($text, "striptags");
        $this->assertEquals("Test paragraph. Other text", $result);

        $result = $filter->sanitize("HELLO", "lower");
        $this->assertEquals("hello", $result);

        $result = $filter->sanitize("hello", "upper");
        $this->assertEquals("HELLO", $result);

        $result = $filter->sanitize("some(one)@exa\\mple.com", "email");
        $this->assertEquals("someone@example.com", $result);

        $text = "http:://www.soliphp.coêèém";
        $result = $filter->sanitize($text, "url");
        $this->assertEquals("http:://www.soliphp.com", $result);

        $text = "Is Peter <smart> & funny?";
        $result = $filter->sanitize($text, "special_chars");
        $this->assertEquals("Is Peter &#60;smart&#62; &#38; funny?", $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidArgumentException()
    {
        $filter = new Filter();
        $filter->sanitize("100", "InvalidArgument");
    }
}
