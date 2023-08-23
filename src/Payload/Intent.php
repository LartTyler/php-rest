<?php

	namespace DaybreakStudios\Rest\Payload;

	enum Intent: string {
		case Create = 'create';
		case Update = 'update';
		case Delete = 'delete';
		case Clone = 'clone';
	}
