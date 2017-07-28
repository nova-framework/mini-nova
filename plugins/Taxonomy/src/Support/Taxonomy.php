<?php namespace Taxonomy\Support;

use Mini\Database\ORM\ModelNotFoundException;

use Taxonomy\Exceptions\VocabularyExistsException;
use Taxonomy\Models\Term;
use Taxonomy\Models\TermRelation;
use Taxonomy\Models\Vocabulary;


class Taxonomy
{
    protected $vocabulary;

    protected $term;

    protected $termRelation;


    public function __construct()
    {
        // Inject required Models.
        $this->vocabulary = new Vocabulary();

        $this->term = new Term();

        $this->termRelation = new TermRelation();
    }

    /**
     * Create a new Vocabulary with the given name
     *
     * @param string $name  The name of the Vocabulary
     *
     * @return mixed  The Vocabulary object if created, FALSE if error creating, Exception if the vocabulary name already exists.
     */
    public function createVocabulary($name)
    {
        $count = $this->vocabulary->where('name', $name)->count();

        if ($count > 0) {
            throw new VocabularyExistsException();
        }

        return $this->vocabulary->create(array('name' => $name));
    }

    /**
     * Get a Vocabulary by ID
     *
     * @param int $id
     *    The id of the Vocabulary to fetch
     *
     * @return
     *    The Vocabulary Model object, otherwise NULL
     */
    public function getVocabulary($id)
    {
        return $this->vocabulary->find($id);
    }

    /**
     * Get a Vocabulary by name
     *
     * @param string $name
     *    The name of the Vocabulary to fetch
     *
     * @return
     *    The Vocabulary Model object, otherwise NULL
     */
    public function getVocabularyByName($name)
    {
        return $this->vocabulary->where('name', $name)->first();
    }

    /**
     * Get a Vocabulary by name
     *
     * @param string $name
     *    The name of the Vocabulary to fetch
     *
     * @return  The Vocabulary Model object, otherwise NULL
     */
    public function getVocabularyByNameAsArray($name)
    {
        $vocabulary = $this->vocabulary->where('name', $name)->first();

        if (! is_null($vocabulary)) {
            return $vocabulary->terms->lists('name', 'id');
        }

        return array();
    }

    /**
     * Get a Vocabulary by name as an options array for dropdowns
     *
     * @param string $id  The ID of the Vocabulary to fetch
     *
     * @return  The Vocabulary Model object, otherwise NULL
     */
    public function getVocabularyTermsAsOptionsArray($id)
    {
        $vocabulary = ($id instanceof Vocabulary)
            ? $id
            : $this->vocabulary->where('id', $id)->first();

        if (is_null($vocabulary)) {
            return collect();
        }

        $terms = $this->term->with('children')
            ->where('parent_id', 0)
            ->where('vocabulary_id', $vocabulary->id)
            ->orderBy('weight', 'ASC')
            ->get();

        //
        $options = array(
            0 => '-',
        );

        foreach ($terms as $term) {
            $key = $term->id;

            $options[$key] = $term->name;

            if (! $term->children->isEmpty()) {
                $this->recurseTermChildren($term, $options);
            }
        }

        return $options;
    }

    /**
     * Recursively visit the children of a term and generate the '- ' option array for dropdowns
     *
     * @param Object $parent
     * @param array  $options
     * @param int    $depth
     *
     * @return array
     */
    private function recurseTermChildren($term, &$options, $depth = 1)
    {
        $term->children->map(function($term) use (&$options, $depth)
        {
            $key = $term->id;

            $options[$key] = str_repeat('-', $depth) .' '. $term->name;

            //
            $term->load('children');

            if (! $term->children->isEmpty()) {
                $this->recurseTermChildren($term, $options, $depth + 1);
            }
        });
    }

    /**
     * Delete a Vocabulary by ID
     *
     * @param int $id
     *    The ID of the Vocabulary to delete
     *
     * @return bool
     *    TRUE if Vocabulary is deletes, otherwise FALSE
     *
     * @throws Mini\Database\ORM\ModelNotFoundException
     */
    public function deleteVocabulary($id)
    {
        $vocabulary = $this->vocabulary->findOrFail($id);

        return $vocabulary->delete();
    }

    /**
     * Delete a Vocabulary by ID
     *
     * @param int $id
     *    The ID of the Vocabulary to delete
     *
     * @return bool
     *    TRUE if Vocabulary is deletes, otherwise FALSE
     *
     * @throws Mini\Database\ORM\ModelNotFoundException
     */
    public function deleteVocabularyByName($name)
    {
        $vocabulary = $this->vocabulary->where('name', $name)->first();

        if (!is_null($vocabulary)) {
            return $vocabulary->delete();
        }

        return FALSE;
    }

    /**
     * Create a new term in a specific vocabulary
     *
     * @param int $vid  The Vocabulary ID in which to add the term
     * @param string $name  The name of the term
     * @param int $parent  The ID of the parent term if it is a child
     * @param int $weight  The weight of the term in order to sort them inside the Vocabulary
     *
     * @return int  The ID of the created term
     *
     * @throws Mini\Database\ORM\ModelNotFoundException
     */
    public function createTerm($vid, $name, $parent = 0, $weight = 0)
    {
        $vocabulary = $this->vocabulary->findOrFail($vid);

        $term = array(
            'name'            => $name,
            'vocabulary_id' => $vid,
            'parent_id'        => $parent,
            'weight'        => $weight,
        );

        return $this->term->create($term);
    }

    /**
     * Delete a Term by ID
     *
     * @param int $id  The ID of the Term to delete
     *
     * @return bool  TRUE if Vocabulary is deletes, otherwise FALSE
     *
     * @throws Mini\Database\ORM\ModelNotFoundException
     */
    public function deleteTerm($id)
    {
        $term = $this->term->with('children')->findOrFail($id);

        foreach ($term->children as $child) {
            $child->parent_id = $term->parent_id;

            $child->save();
        }

        $this->termRelation->where('term_id', $term->id)->delete();

        return $term->delete();
    }

    /**
     * Update the Terms order in a Vocabulary.
     *
     */
    public function updateTermsOrder(array $items, $parentId = 0)
    {
        foreach ($items as $weight => $item) {
            try {
                $term = $this->term->findOrFail($item->id);
            }
            catch (ModelNotFoundException $e) {
                continue;
            }

            $term->parent_id = $parentId;

            $term->weight = $weight;

            $term->save();

            if (! empty($item->children)) {
                $this->updateTermsOrder($item->children, $term->id);
            }
        }
    }
}
