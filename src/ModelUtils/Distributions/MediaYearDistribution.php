<?php
class MediaYearDistribution extends AbstractDistribution
{
	protected function sortGroups()
	{
		krsort($this->groups, SORT_NUMERIC);
		krsort($this->entries, SORT_NUMERIC);
	}

	public function addEmptyYears()
	{
		if (!empty($this->keys))
		{
			$min = $max = reset($this->keys);
			while (list($i,) = each($this->keys))
			{
				if ($min > $i)
				{
					$min = $i;
				}
				elseif ($max < $i)
				{
					$max = $i;
				}
			}
			for ($i = $min + 1; $i < $max; $i ++)
			{
				$this->addGroup($i);
			}
		}
	}

	public function getNullGroupKey()
	{
		return 0;
	}

	public static function getPublishedYear($entry)
	{
		$yearA = intval(substr($entry->published_from, 0, 4));
		$yearB = intval(substr($entry->published_to, 0, 4));
		if (!$yearA and !$yearB)
		{
			return 0;
		}
		elseif (!$yearA)
		{
			return $yearB;
		}
		return $yearA;
	}

	public function addEntry($entry)
	{
		$this->addToGroup(self::getPublishedYear($entry), $entry);
	}
}