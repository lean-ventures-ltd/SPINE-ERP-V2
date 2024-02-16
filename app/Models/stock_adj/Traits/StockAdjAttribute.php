<?php

namespace App\Models\stock_adj\Traits;

trait StockAdjAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-opening-stock", "biller.stock_adjs.show")
        .' '. $this->getEditButtonAttribute("edit-opening-stock", "biller.stock_adjs.edit")
        .' '.$this->getDeleteButtonAttribute("delete-opening-stock", "biller.stock_adjs.destroy");     
    }
}
