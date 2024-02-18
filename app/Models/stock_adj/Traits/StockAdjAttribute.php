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

    /**
     * Adjustment Type Attribute
     */
    public function getAdjustmentTypeAttribute()
    {
        $label = '';
        if ($this->adj_type == 'Qty') $label = 'Quantity';
        if ($this->adj_type == 'Cost') $label = 'Cost';
        if ($this->adj_type == 'Qty-Cost') $label = 'Cost & Quantity';
        return $label;
    }
}
