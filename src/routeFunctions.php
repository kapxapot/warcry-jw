<?php

use Warcry\Exceptions\BadRequestException;

function heroesStringToIds($container, $heroesByName, $string) {
	$names = explode(';', $string);
	$ids = [];

	foreach ($names as $name) {
		if (isset($heroesByName[$name])) {
			$ids[] = $heroesByName[$name]['id'];
		}
		else {
			$container->logger->addError("Не найден герой с англ. именем {$name}.");
			throw new ApplicationException('Внутренняя ошибка.');
		}
	}

	sort($ids);
	
	return $ids;
}

function idsToHeroesString($container, $ids) {
	$names = [];
	
	foreach ($ids as $id) {
		$hero = getHeroById($container, $id);

		if ($hero) {
			$names[] = $hero['name_en'];
		}
		else {
			$container->logger->addError("Не найден герой: {$id}.");
			throw new ApplicationException('Внутренняя ошибка.');
		}
	}

	sort($names);
	
	return implode(';', $names);
}

function idsToString($ids) {
	sort($ids);
	
	return implode('_', $ids);
}

function stringToIds($string) {
	$chunks = explode('_', $string);
	$ids = [];
	
	foreach ($chunks as $chunk) {
		if (is_numeric($chunk)) {
			$ids[] = $chunk;
		}
		else {
			throw new BadRequestException('Идентификаторы должны быть целочисленными.');
		}
	}
	
	sort($ids);
	
	return $ids;
}

function idsToHeroesHumanString($container, $ids) {
	$heroes = [];
	foreach ($ids as $id) {
		$hero = getHeroById($container, $id);
		$heroes[] = $hero['name'];
	}

	sort($heroes);

	return implode(', ', $heroes);
}

function prepareCounterPick($container, $c) {
	$arr = is_array($c) ? $c : $c->as_array();

	$pickIds = stringToIds($c['pick_ids']);
	$arr['pick_string'] = idsToHeroesHumanString($container, $pickIds);
	
	$pickHeroes = [];
	foreach ($pickIds as $id) {
		$pickHeroes[] = getHeroById($container, $id);
	}
	
	$func = function ($a, $b) { return strcmp($a['name'], $b['name']); };
	
	usort($pickHeroes, $func);

	$arr['pick_heroes'] = $pickHeroes;

	$counterPickIds = stringToIds($c['counter_pick_ids']);
	$arr['counter_pick_string'] = idsToHeroesHumanString($container, $counterPickIds);

	$counterPickHeroes = [];
	foreach ($counterPickIds as $id) {
		$counterPickHeroes[] = getHeroById($container, $id);
	}
	
	usort($counterPickHeroes, $func);
	
	$arr['counter_pick_heroes'] = $counterPickHeroes;

	return $arr;
}

function getHeroById($container, $id) {
	return $container->db->getEntityById('heroes', $id);
}
