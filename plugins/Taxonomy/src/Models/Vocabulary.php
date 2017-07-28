<?php

namespace Taxonomy\Models;

use Mini\Database\ORM\Model as BaseModel;

use Taxonomy\Support\Traits\UniqueSlugTrait;


class Vocabulary extends BaseModel
{
    use UniqueSlugTrait;

    //
    protected $table = 'vocabularies';

    protected $primaryKey = 'id';

    protected $fillable = array(
        'name', 'description'
    );

    protected $hidden = array(
        'created_at','updated_at'
    );


    public function terms()
    {
        return $this->hasMany('Taxonomy\Models\Term');
    }

    public function relations()
    {
        return $this->hasMany('Taxonomy\Models\TermRelation');
    }
}
