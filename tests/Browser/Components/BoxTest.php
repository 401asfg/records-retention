<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BoxTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->click("@add-box");
        });
    }

    public function testAllFieldsInitiallyEmpty()
    {
        // FIXME: is this valid with cookies?
        $this->browse(function (Browser $browser) {
            $browser->assertValue("@box-1-description", "")
                ->assertRadioSelected("@box-1-final-disposition-shred", "shred")
                ->assertRadioNotSelected("@box-1-final-disposition-permanent-storage", "permanent-storage")
                ->assertValue("@box-1-destroy-date", "");
        });
    }

    public function testNoRemoveButtonOnNullRemoveBox()
    {
        $this->browse(function (Browser $browser) {
            $browser->assertMissing("@box-1-remove-button");
        });
    }

    public function testRemoveButtonVisibleOnNonNullRemoveBox()
    {
        $this->browse(function (Browser $browser) {
            $browser->assertVisible("@box-1-remove-button");
        });
    }
}
