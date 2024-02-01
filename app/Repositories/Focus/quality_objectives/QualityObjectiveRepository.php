<?php

namespace App\Repositories\Focus\quality_objectives;

use App\Exceptions\GeneralException;
use App\Models\quality_objectives\QualityObjective;
use App\Repositories\BaseRepository;

class QualityObjectiveRepository extends BaseRepository
{
    const MODEL = QualityObjective::class;

    public function getForDataTable()
    {

        return $this->query()->get();
    }

    public function create(array $input)
    {
        if (QualityObjective::create($input)) {
            return true;
        }
        throw new GeneralException('Error creating quality objective');
    }

    public function update($qualityObjective, array $input)
    {
         
        if ($qualityObjective->update($input))
            return true;

        throw new GeneralException('Error updating quality objective');
    }

}
