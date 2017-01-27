<?php
namespace Sichikawa\LaravelSendgridDriver\Api;

class Asm
{
    /**
     * @var int
     */
    public $group_id;

    /**
     * @var array[int]
     */
    public $groups_to_display;

    /**
     * @param int $group_id
     * @return Asm
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
        return $this;
    }

    /**
     * @param array $groups_to_display
     * @return Asm
     */
    public function setGroupsToDisplay($groups_to_display)
    {
        $this->groups_to_display = $groups_to_display;
        return $this;
    }

    public function toArray()
    {
        return array_filter(json_decode(json_encode($this), true), function ($val) {
            return !empty($val);
        });
    }
}