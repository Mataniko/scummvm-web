<?php
namespace ScummVM\Objects;

/**
 * The BaseSection class represents a section (or a subsection) on the credits page
 * on the website.
 */
class CreditsSection extends BaseSection
{
    private $groups;
    private $paragraphs;

    /* CreditsSection object constructor. */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->groups = [];
        $this->paragraphs = [];

        if (isset($data['group'])) {
            foreach ($data['group'] as $value) {
                $persons = [];
                foreach ($value['person'] as $args) {
                    $persons[] = new Person($args);
                }
                if (count($persons) > 0) {
                    $this->groups[] = array(
                        'name' => $value['name'],
                        'persons' => $persons,
                    );
                }
            }
        }
        if (isset($data['paragraph'])) {
            $this->paragraphs = $data['paragraph'];
        }
    }

    /* Get the optional list of groups. */
    public function getGroups()
    {
        return $this->groups;
    }

    /* Get the optional list of paragraphs. */
    public function getParagraphs()
    {
        return $this->paragraphs;
    }
}
