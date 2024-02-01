<?php

namespace App\Models\manualjournal\Traits;

trait JournalAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return ' '. $this->getViewButtonAttribute("business_settings", "biller.journals.show") . 
            ' '. $this->getDeleteButtonAttribute("business_settings", "biller.journals.destroy");
    }
}
