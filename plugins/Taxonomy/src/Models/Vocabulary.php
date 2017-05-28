<?php

namespace Taxonomy\Models;

use Mini\Database\ORM\Model as BaseModel;

use Taxonomy\Models\Term;


class Vocabulary extends BaseModel
{
	protected $table = 'vocabularies';

	protected $primaryKey = 'id';

	protected $fillable = array(
		'name', 'description'
	);

	protected $hidden = array('created_at','updated_at');

	public static $rules = array(
		'name' => 'required'
	);


	public function terms()
	{
		return $this->hasMany('Taxonomy\Models\Term');
	}

	public function relations()
	{
		return $this->hasMany('Taxonomy\Models\TermRelation');
	}

	public static function updateTermsOrder(array $items, $parentId = 0)
	{
		foreach ($items as $weight => $item) {
			try {
				$term = Term::findOrFail($item->id);
			}
			catch (ModelNotFoundException $e) {
				continue;
			}

			$term->parent_id = $parentId;

			$term->weight = $weight;

			$term->save();

			if (! empty($item->children)) {
				static::updateTermsOrder($item->children, $term->id);
			}
		}
	}
}
