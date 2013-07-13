<?php
class MediaSubProcessorBasic extends MediaSubProcessor
{
	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		if ($xpath->query('//div[@class = \'badresult\']')->length >= 1)
		{
			throw new BadDocumentNodeException($documents[self::URL_MEDIA], 'badresult', '');
		}

		$title = Strings::removeSpaces(self::getNodeValue($xpath, '//h1/*/following-sibling::node()[1][self::text()]'));
		if (empty($title))
		{
			throw new BadDocumentNodeException($documents[self::URL_MEDIA], 'title', '');
		}

		//sub type
		$malSubType = strtolower(Strings::removeSpaces(self::getNodeValue($xpath, '//span[starts-with(text(), \'Type\')]/following-sibling::node()[self::text()]')));
		$subType = Strings::makeEnum($malSubType, [
			'tv'       => AnimeMediaType::TV,
			'ova'      => AnimeMediaType::OVA,
			'movie'    => AnimeMediaType::Movie,
			'special'  => AnimeMediaType::Special,
			'ona'      => AnimeMediaType::ONA,
			'music'    => AnimeMediaType::Music,
			'manga'    => MangaMediaType::Manga,
			'novel'    => MangaMediaType::Novel,
			'one shot' => MangaMediaType::OneShot,
			'doujin'   => MangaMediaType::Doujin,
			'manhwa'   => MangaMediaType::Manhwa,
			'manhua'   => MangaMediaType::Manhua,
			'oel'      => MangaMediaType::OEL,
		], null);
		if ($subType === null)
		{
			throw new BadDocumentNodeException($documents[self::URL_MEDIA], 'sub-type', $malSubType);
		}

		//mal id
		$malId  = self::getNodeValue($xpath, '//input[starts-with(@id, \'myinfo_\')]', null, 'value');

		//picture
		$pictureUrl = self::getNodeValue($xpath, '//td[@class = \'borderClass\']//img', null, 'src');

		//rank
		preg_match_all('/#([0-9]+)/', self::getNodeValue($xpath, '//h1/*'), $matches);
		$ranking = Strings::makeInteger($matches[1][0]);

		//status
		$malStatus = strtolower(Strings::removeSpaces(self::getNodeValue($xpath, '//span[starts-with(text(), \'Status\')]/following-sibling::node()[self::text()]')));
		$status = Strings::makeEnum($malStatus, [
			'not yet published' => MediaStatus::NotYetPublished,
			'not yet aired'     => MediaStatus::NotYetPublished,
			'publishing'        => MediaStatus::Publishing,
			'currently airing'  => MediaStatus::Publishing,
			'finished'          => MediaStatus::Finished,
			'finished airing'   => MediaStatus::Finished,
		], null);
		if ($status === null)
		{
			throw new BadDocumentNodeException($documents[self::URL_MEDIA], 'status', $malStatus);
		}

		//air dates
		$publishedString = Strings::removeSpaces(self::getNodeValue($xpath, '//span[starts-with(text(), \'Aired\') or starts-with(text(), \'Published\')]/following-sibling::node()[self::text()]'));
		$pos = strrpos($publishedString, ' to ');
		if ($pos !== false)
		{
			$publishedFrom = Strings::makeDate(substr($publishedString, 0, $pos));
			$publishedTo = Strings::makeDate(substr($publishedString, $pos + 4));
		}
		else
		{
			$publishedFrom = Strings::makeDate($publishedString);
			$publishedTo = Strings::makeDate($publishedString);
		}

		$this->delete('media', ['mal_id' => $malId, 'media' => $this->media]);
		$mediaId = $this->insert('media', [
			'mal_id' => $malId,
			'media' => $this->media,
			'title' => $title,
			'sub_type' => $subType,
			'picture_url' => $pictureUrl,
			'ranking' => $ranking,
			'status' => $status,
			'published_from' => $publishedFrom,
			'published_to' => $publishedTo
		]);
		$context->mediaId = $mediaId;
	}
}