<?php

namespace Taxonomy\Models;

use Mini\Database\ORM\Model as BaseModel;


class TermRelation extends BaseModel
{
	protected $table = 'term_relations';

	protected $primaryKey = 'id';

	protected $fillable = array(
		'term_id', 'vocabulary_id',
	);


	public function relationable()
	{
		return $this->morphTo();
	}

	public function term()
	{
		return $this->belongsTo('Taxonomy\Models\Term');
	}

	public function vocabulary()
	{
		return $this->belongsTo('Taxonomy\Models\Vocabulary');
	}
}
