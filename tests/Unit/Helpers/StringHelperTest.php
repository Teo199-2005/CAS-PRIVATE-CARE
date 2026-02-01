<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Helpers\StringHelper;

/**
 * Test cases for StringHelper utility class
 */
class StringHelperTest extends TestCase
{
    /**
     * Test slug generation
     */
    public function test_slug_creates_url_friendly_string(): void
    {
        $this->assertEquals('hello-world', StringHelper::slug('Hello World'));
        $this->assertEquals('test-string', StringHelper::slug('Test String!'));
        $this->assertEquals('multiple-spaces', StringHelper::slug('Multiple    Spaces'));
        $this->assertEquals('special-chars', StringHelper::slug('Special @#$% Chars'));
    }

    /**
     * Test slug with custom separator
     */
    public function test_slug_with_custom_separator(): void
    {
        $this->assertEquals('hello_world', StringHelper::slug('Hello World', '_'));
    }

    /**
     * Test string truncation
     */
    public function test_truncate_shortens_string(): void
    {
        $text = 'This is a very long string that should be truncated';
        $this->assertEquals('This is a ...', StringHelper::truncate($text, 13));
    }

    /**
     * Test truncation of short strings
     */
    public function test_truncate_leaves_short_strings_unchanged(): void
    {
        $text = 'Short';
        $this->assertEquals('Short', StringHelper::truncate($text, 20));
    }

    /**
     * Test word-boundary truncation
     */
    public function test_truncate_words_respects_word_boundaries(): void
    {
        $text = 'This is a test sentence for word truncation';
        $result = StringHelper::truncateWords($text, 20);
        $this->assertStringEndsWith('...', $result);
        $this->assertLessThanOrEqual(20, mb_strlen($result));
    }

    /**
     * Test title case conversion
     */
    public function test_title_case_converts_properly(): void
    {
        $this->assertEquals('Hello World', StringHelper::titleCase('hello world'));
        $this->assertEquals('John Doe', StringHelper::titleCase('john doe'));
    }

    /**
     * Test filename sanitization
     */
    public function test_sanitize_filename_removes_dangerous_chars(): void
    {
        $this->assertEquals('test_file.pdf', StringHelper::sanitizeFilename('test file.pdf'));
        $this->assertEquals('document.pdf', StringHelper::sanitizeFilename('../../../document.pdf'));
        $this->assertEquals('file_with_special_.txt', StringHelper::sanitizeFilename('file with special!@#.txt'));
    }

    /**
     * Test initials extraction
     */
    public function test_initials_extracts_correctly(): void
    {
        $this->assertEquals('JD', StringHelper::initials('John Doe'));
        $this->assertEquals('JA', StringHelper::initials('John Adam Smith', 2));
        $this->assertEquals('J', StringHelper::initials('John', 2));
    }

    /**
     * Test string masking
     */
    public function test_mask_hides_middle_characters(): void
    {
        // 'hello123' is 8 chars: 'he' + 4 masked + '23' = 'he****23'
        $this->assertEquals('he****23', StringHelper::mask('hello123', 2, 2));
        // 'abcdefghcd' is 10 chars: 'ab' + 6 masked + 'cd' = 'ab******cd'
        $this->assertEquals('ab******cd', StringHelper::mask('abcdefghcd', 2, 2));
        // Test with short string - all masked
        $this->assertEquals('***', StringHelper::mask('abc', 2, 2));
    }

    /**
     * Test email masking
     */
    public function test_mask_email_hides_local_part(): void
    {
        $result = StringHelper::maskEmail('john.doe@example.com');
        $this->assertStringContainsString('@example.com', $result);
        $this->assertStringContainsString('*', $result);
    }

    /**
     * Test phone masking
     */
    public function test_mask_phone_shows_last_four(): void
    {
        $this->assertStringEndsWith('1234', StringHelper::maskPhone('555-123-1234'));
        $this->assertStringContainsString('*', StringHelper::maskPhone('555-123-1234'));
    }

    /**
     * Test phone formatting
     */
    public function test_format_phone_creates_standard_format(): void
    {
        $this->assertEquals('(555) 123-4567', StringHelper::formatPhone('5551234567'));
        $this->assertEquals('(555) 123-4567', StringHelper::formatPhone('1-555-123-4567'));
        $this->assertEquals('(555) 123-4567', StringHelper::formatPhone('555.123.4567'));
    }

    /**
     * Test excerpt generation
     */
    public function test_excerpt_strips_html(): void
    {
        $html = '<p>This is a <strong>test</strong> paragraph.</p>';
        $excerpt = StringHelper::excerpt($html, 50);
        $this->assertStringNotContainsString('<p>', $excerpt);
        $this->assertStringNotContainsString('<strong>', $excerpt);
    }

    /**
     * Test containsAny
     */
    public function test_contains_any_finds_matches(): void
    {
        $this->assertTrue(StringHelper::containsAny('Hello World', ['world', 'test']));
        $this->assertFalse(StringHelper::containsAny('Hello World', ['foo', 'bar']));
    }

    /**
     * Test case-sensitive containsAny
     */
    public function test_contains_any_case_sensitive(): void
    {
        $this->assertFalse(StringHelper::containsAny('Hello World', ['WORLD'], true));
        $this->assertTrue(StringHelper::containsAny('Hello World', ['World'], true));
    }
}
