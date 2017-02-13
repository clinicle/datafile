<?php
/*
 * This file is part of the ClinicLE package.
 *
 * (c) Rob Free <rob@clinicle.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ClinicLE\Test\DataFile;
use ClinicLE\DataFile\FieldTextConverter;

class FieldTextConverterTest extends \PHPUnit_Framework_TestCase
{
    private $SUT;
    const TITLE = 'This is just a title';
    const TITLE_AS_FIELD = 'this_is_just_a_title';
    const TYPE = 'radio_buttons';
    const TYPE2 = 'horiz_container';
    const FIELD = 'only_a_title';

    const LINE_COMPLEX = 'Have you ever kept, or cared for, birds including caged birds or racing birds?* $ever_kept_birds :yes_no {condition_test=Y,condition_action=show,condition_target=bird_description}';
    const TITLE_COMPLEX = 'Have you ever kept, or cared for, birds including caged birds or racing birds?';
    const FIELD_COMPLEX = 'ever_kept_birds';
    const TYPE_COMPLEX = 'yes_no';
    const SETTINGS_COMPLEX = 'condition_test=Y,condition_action=show,condition_target=bird_description';

    const LINE_LONG = 'Have you ever kept, or cared for, birds including caged birds or racing birds?* :yes_no {condition_test=Y,condition_action=show,condition_target=bird_description}';
    const TITLE_LONG = 'Have you ever kept, or cared for, birds including caged birds or racing birds?';
    const FIELD_LONG = 'have_you_ever_kept_or_cared_fo';

    public function setUp()
    {
        $this->SUT = new FieldTextConverter();
    }
    /**
     * @test
     */
    public function it_should_convert_text_to_title_and_name()
    {
        $converted = $this->SUT->convert(self::TITLE);
        $this->assertEquals(array('Level' => 1, 'Title' => self::TITLE, 'Field' => self::TITLE_AS_FIELD, 'Required' => '', 'Type' => 'markup', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_asterisk_as_required_field()
    {
        $converted = $this->SUT->convert(self::TITLE.'*');
        $this->assertEquals(array('Level' => 1, 'Title' => self::TITLE, 'Field' => self::TITLE_AS_FIELD, 'Required' => 1, 'Type' => 'markup', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_colon_prefix_as_field_type()
    {
        $converted = $this->SUT->convert(self::TITLE.'* :'.self::TYPE);
        $this->assertEquals(array('Level' => 1, 'Title' => self::TITLE, 'Field' => self::TITLE_AS_FIELD, 'Type' => self::TYPE, 'Required' => 1, 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_dollar_prefix_as_field_name()
    {
        $converted = $this->SUT->convert(self::TITLE.'* :'.self::TYPE.' $'.self::FIELD);

        $this->assertEquals(array('Level' => 1, 'Title' => self::TITLE, 'Field' => self::FIELD, 'Required' => 1, 'Type' => self::TYPE, 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_curly_brackets_as_settings()
    {
        $converted = $this->SUT->convert(self::TITLE.'* :'.self::TYPE.' $'.self::FIELD.' {min=1, max=5}');
        $this->assertEquals(array('Level' => 1, 'Title' => self::TITLE, 'Field' => self::FIELD, 'Required' => 1, 'Type' => self::TYPE, 'Settings' => 'min=1, max=5', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_square_brackets_as_options()
    {
        $converted = $this->SUT->convert(self::TITLE.'* :'.self::TYPE.' $'.self::FIELD.' [Y=Yes, N=No]');
        $this->assertEquals(array('Level' => 1, 'Title' => self::TITLE, 'Field' => self::FIELD, 'Type' => self::TYPE, 'Required' => 1, 'Settings' => '', 'Options' => 'Y=Yes, N=No'), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_only_field_type()
    {
        $converted = $this->SUT->convert(':'.self::TYPE2);
        $this->assertEquals(array('Level' => 1, 'Title' => '', 'Field' => '', 'Type' => self::TYPE2, 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_return_level_3_when_2_tabs_at_beginning_of_line()
    {
        $converted = $this->SUT->convert("\t\t".self::TITLE);
        $this->assertEquals(array('Level' => 3, 'Title' => self::TITLE, 'Field' => self::TITLE_AS_FIELD, 'Type' => 'markup', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_return_level_2_when_1_tab_at_beginning_of_line()
    {
        $converted = $this->SUT->convert("\t".self::TITLE);
        $this->assertEquals(array('Level' => 2, 'Title' => self::TITLE, 'Field' => self::TITLE_AS_FIELD, 'Type' => 'markup', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_convert_a_complex_fieldtext_line()
    {
        $converted = $this->SUT->convert("\t".self::LINE_COMPLEX);
        $this->assertEquals(array('Level' => 2, 'Title' => self::TITLE_COMPLEX, 'Field' => self::FIELD_COMPLEX, 'Type' => self::TYPE_COMPLEX, 'Required' => 1, 'Settings' => self::SETTINGS_COMPLEX, 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_truncate_if_title_is_too_long_to_create_name()
    {
        $converted = $this->SUT->convert("\t".self::LINE_LONG);
        $this->assertEquals(array('Level' => 2, 'Title' => self::TITLE_LONG, 'Field' => self::FIELD_LONG, 'Type' => self::TYPE_COMPLEX, 'Required' => 1, 'Settings' => self::SETTINGS_COMPLEX, 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_allow_special_characters_in_title_if_in_quotes()
    {
        $line = '"Enter the prices [in $]" $price_entry :number {prefix=£}';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 1, 'Title' => 'Enter the prices [in $]', 'Field' => 'price_entry', 'Type' => 'number', 'Required' => '', 'Settings' => 'prefix=£', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_space_as_well_as_tab_for_level()
    {
        $line = '  Enter the prices $price_entry :number {prefix=£}';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 3, 'Title' => 'Enter the prices', 'Field' => 'price_entry', 'Type' => 'number', 'Required' => '', 'Settings' => 'prefix=£', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_allow_expression_with_brackets()
    {
        $line = "   BMI \$bmi :expression {expression=(get('height')/get('weight'))}";
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 4, 'Title' => 'BMI', 'Field' => 'bmi', 'Type' => 'expression', 'Required' => '', 'Settings' => "expression=(get('height')/get('weight'))", 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_allow_html_markup()
    {
        $line = '   <p>Whatever</p>';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 4, 'Title' => '<p>Whatever</p>', 'Field' => 'whatever', 'Type' => 'markup', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     * @expectedException ClinicLE\DataFile\Exception\FieldTooLongException
     */
    public function it_should_fail_if_the_field_is_greater_than_40_characters_long()
    {
        $line = '				:dropdown $eswt_reason_for_termination_pre_extra_length_field [SOB=SOB,speed=speed,leg fatigue=leg fatigue,joint pain=joint pain,test complete=test complete,other=other]';
        $this->SUT->convert($line);
    }

    public function it_should_convert_special_characters_in_title_correctly()
    {
        $line = '   Sjögren\'s syndrome :radio_item $condition_ssyndrome';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 4, 'Title' => 'Sjögren\'s syndrome', 'Field' => 'condition_ssyndrome', 'Type' => 'radio_item', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_allow_html_markup2()
    {
        $line = '		<b>Reason for ESWT termination</b> :markup $eswt_reason_mkup';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 3, 'Title' => '<b>Reason for ESWT termination</b>', 'Field' => 'eswt_reason_mkup', 'Type' => 'markup', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_dash_as_delete()
    {
        $line = '		-<b>Reason for ESWT termination</b> :markup $eswt_reason_mkup';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => 'DEL', 'Title' => '<b>Reason for ESWT termination</b>', 'Field' => 'eswt_reason_mkup', 'Type' => 'markup', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_at_sign_as_rename()
    {
        $line = '		<b>Reason for ESWT termination</b> :markup $eswt_reason_term @eswt_reason_mkup';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Rename' => 'eswt_reason_mkup', 'Level' => '3', 'Title' => '<b>Reason for ESWT termination</b>', 'Field' => 'eswt_reason_term', 'Type' => 'markup', 'Required' => '', 'Settings' => '', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_at_sign_as_rename_with_numbers_in_field_name()
    {
        $line = '		* @iswt1_distance_prac $iswt_distance_prac :number {size=6,suffix=m,min=0,max=1200}';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Rename' => 'iswt1_distance_prac', 'Level' => '3', 'Title' => '', 'Field' => 'iswt_distance_prac', 'Type' => 'number', 'Required' => '1', 'Settings' => 'size=6,suffix=m,min=0,max=1200', 'Options' => ''), $converted);
    }

    /**
     * @test
     */
    public function it_should_recognise_asterisk_to_designate_required_field_and_an_asterisk_used_in_settings()
    {
        $line = '		ISWT total $iswt_total :expression {size=6,suffix=m,min=0,max=1200,expression=iswt_distance * iswt_score}';
        $converted = $this->SUT->convert($line);
        $this->assertEquals(array('Level' => '3', 'Title' => 'ISWT total', 'Field' => 'iswt_total', 'Type' => 'expression', 'Settings' => 'size=6,suffix=m,min=0,max=1200,expression=iswt_distance * iswt_score', 'Options' => '', 'Required' => ''), $converted);
    }
}
