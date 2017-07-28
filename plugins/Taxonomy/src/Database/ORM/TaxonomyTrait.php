<?php

namespace Taxonomy\Database\ORM;

use Taxonomy\Models\TermRelation;
use Taxonomy\Models\Term;
use Taxonomy\Support\Facades\Taxonomy;


trait TaxonomyTrait
{
    /**
     * Return collection of tags related to the tagged model
     *
     * @return Mini\Database\ORM\Collection
     */
    public function related()
    {
        return $this->morphMany('Taxonomy\Models\TermRelation', 'relationable');
    }

    /**
     * Add an existing term to the inheriting model
     *
     * @param $termId int  The ID of the term to link
     *
     * @return object  The TermRelation object
     */
    public function addTerm($termId)
    {
        $term = Term::findOrFail($termId);

        $attributes = array(
            'term_id'        => $term->id,
            'vocabulary_id' => $term->vocabulary_id,
        );

        $this->related()->save(new TermRelation($attributes));
    }

    /**
     * Check if the Model instance has the passed term as an existing relation
     *
     * @param mixed $termId
     *  The ID of the term or an instance of the Term object
     *
     * @return object
     *  The TermRelation object
     */
    public function hasTerm($termId)
    {
        $term = ($termId instanceof Term) ? $termId : Term::findOrFail($termId);

        $count = $this->related()->where('term_id', $termId)->count();

        return ($count > 0);
    }

    /**
    * Get all the terms for a given vocabulary that are linked to the current Model.
    *
    * @param $name string  The name of the vocabulary
    *
    * @return object  A collection of \Taxonomy\Models\TermRelation objects
    */
    public function getTermsByVocabularyName($name)
    {
        $vocabulary = Taxonomy::getVocabularyByName($name);

        return $this->related()->where('vocabulary_id', $vocabulary->id)->get();
    }

    /**
    * Get all the terms for a given vocabulary that are linked to the current Model as a key value pair array.
    *
    * @param $name string  The name of the vocabulary
    *
    * @return array  A key value pair array of the type 'id' => 'name'
    */
    public function getTermsByVocabularyNameAsArray($name)
    {
        $vocabulary = Taxonomy::getVocabularyByName($name);

        $termRelations = $this->related()->where('vocabulary_id', $vocabulary->id)->get();

        $data = array();

        foreach ($termRelations as $termRelation) {
            $key = $termRelation->term->id;

            $data[$key] = $termRelation->term->name;
        }

        return $data;
    }

    /**
     * Unlink the given term with the current model object
     *
     * @param $termId int  The ID of the term
     *
     * @return bool  TRUE if the term relation has been deleted, otherwise FALSE
     */
    public function removeTerm($termId)
    {
        return $this->related()->where('term_id', $termId)->delete();
    }

    /**
     * Unlink all the terms from the current model object
     *
     * @return bool  TRUE if the term relation has been deleted, otherwise FALSE
     */
    public function removeAllTerms()
    {
        return $this->related()->delete();
    }

    /**
     * Filter the model to return a subset of entries matching the term ID
     *
     * @param object $query
     * @param int $termId
     *
     * @return void
     */
    public function scopeGetAllByTermId($query, $termId)
    {
        return $query->whereHas('related', function($query) use($termId)
        {
            if (is_array($termId)) {
                return $query->whereIn('term_id', $termId);
            }

            return $query->where('term_id', '=', $termId);
        });
    }
}
