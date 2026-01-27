<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum TransportationMethod: string
{
	case Air             = 'air';
	case Sea             = 'sea';
	case Land            = 'land';
	case Road            = 'road';
	case Rail            = 'rail';
	case Courier         = 'courier';
	case Postal          = 'postal';
	case Express         = 'express';
	case Standard        = 'standard';
	case Expedited       = 'expedited';
	case SameDay         = 'same_day';
	case NextDay         = 'next_day';
	case Freight         = 'freight';
	case Truck           = 'truck';
	case Ship            = 'ship';
	case AirFreight      = 'air_freight';
	case SeaFreight      = 'sea_freight';
	case LCL             = 'lcl'; // Less than Container Load
	case FCL             = 'fcl'; // Full Container Load
	case Parcel          = 'parcel';
	case Pallet          = 'pallet';
	case Container       = 'container';
	case Pipeline        = 'pipeline';
	case HandDelivery    = 'hand_delivery';
	case Pickup          = 'pickup';
	case DropShipping    = 'drop_shipping';
	case CrossDocking    = 'cross_docking';
	case Multimodal      = 'multimodal';
	case Intermodal      = 'intermodal';
	case Other           = 'other';
	case Undefined       = 'undefined';

	public static function normalize(null|string|BackedEnum $v): self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;

		$k = strtolower(trim((string) $v));

		$aliases = [
			// Air transport aliases
			'airplane'        => 'air',
			'aeroplane'       => 'air',
			'aircraft'        => 'air',
			'aviation'        => 'air',
			'air_cargo'       => 'air_freight',
			'airfreight'      => 'air_freight',

			// Sea transport aliases
			'ocean'           => 'sea',
			'marine'          => 'sea',
			'vessel'          => 'ship',
			'boat'            => 'ship',
			'cargo_ship'      => 'sea_freight',
			'seafreight'      => 'sea_freight',
			'container_ship'  => 'sea_freight',

			// Land transport aliases
			'ground'          => 'land',
			'surface'         => 'land',

			// Road transport aliases
			'highway'         => 'road',
			'motor'           => 'road',
			'vehicle'         => 'road',
			'delivery_van'    => 'truck',
			'lorry'           => 'truck',
			'van'             => 'truck',
			'delivery_truck'  => 'truck',
			'transport_truck' => 'truck',

			// Rail transport aliases
			'train'           => 'rail',
			'railway'         => 'rail',
			'railroad'        => 'rail',

			// Courier aliases
			'courier_service' => 'courier',
			'messenger'       => 'courier',
			'parcel_service'  => 'courier',
			'package_courier' => 'courier',

			// Postal aliases
			'mail'            => 'postal',
			'post'            => 'postal',
			'post_office'     => 'postal',
			'usps'            => 'postal',
			'royal_mail'      => 'postal',

			// Express delivery aliases
			'express_delivery' => 'express',
			'fast_delivery'   => 'express',
			'priority'        => 'express',
			'premium'         => 'express',

			// Standard delivery aliases
			'regular'         => 'standard',
			'economy'         => 'standard',
			'normal'          => 'standard',
			'basic'           => 'standard',

			// Expedited aliases
			'rush'            => 'expedited',
			'urgent'          => 'expedited',
			'priority_delivery' => 'expedited',

			// Same day aliases
			'today'           => 'same_day',
			'instant'         => 'same_day',
			'immediate'       => 'same_day',

			// Next day aliases
			'nextday'         => 'next_day',
			'overnight'       => 'next_day',
			'1_day'           => 'next_day',
			'next_day_delivery' => 'next_day',

			// Freight aliases
			'cargo'           => 'freight',
			'haulage'         => 'freight',
			'bulk_transport'  => 'freight',

			// LCL/FCL aliases
			'less_than_container_load' => 'lcl',
			'groupage'       => 'lcl',
			'full_container_load' => 'fcl',

			// Parcel aliases
			'package'         => 'parcel',
			'small_package'   => 'parcel',
			'small_parcel'    => 'parcel',

			// Pallet aliases
			'palletized'      => 'pallet',
			'pallet_load'     => 'pallet',

			// Container aliases
			'shipping_container' => 'container',
			'iso_container'   => 'container',

			// Pipeline aliases
			'pipe'            => 'pipeline',
			'conduit'         => 'pipeline',

			// Hand delivery aliases
			'personal_delivery' => 'hand_delivery',
			'by_hand'        => 'hand_delivery',
			'manual_delivery' => 'hand_delivery',

			// Pickup aliases
			'customer_pickup' => 'pickup',
			'store_pickup'    => 'pickup',
			'collection'      => 'pickup',
			'self_pickup'     => 'pickup',

			// Drop shipping aliases
			'dropship'        => 'drop_shipping',
			'drop_ship'       => 'drop_shipping',
			'direct_shipping' => 'drop_shipping',

			// Cross docking aliases
			'crossdock'       => 'cross_docking',
			'cross_dock'      => 'cross_docking',

			// Multimodal aliases
			'combined'        => 'multimodal',
			'mixed_mode'      => 'multimodal',

			// Intermodal aliases
			'intermodal_transport' => 'intermodal',

			// Portuguese aliases
			'aereo'           => 'air',
			'maritimo'        => 'sea',
			'terrestre'       => 'land',
			'rodoviario'      => 'road',
			'ferroviario'     => 'rail',
			'correio'         => 'courier',
			'postal_pt'       => 'postal',
			'expresso'        => 'express',
			'padrao'          => 'standard',
			'acelerado'       => 'expedited',
			'mesmo_dia'       => 'same_day',
			'proximo_dia'     => 'next_day',
			'frete'           => 'freight',
			'caminhao'        => 'truck',
			'navio'           => 'ship',
			'carga_aerea'     => 'air_freight',
			'carga_maritima'  => 'sea_freight',
			'pacote'          => 'parcel',
			'palete'          => 'pallet',
			'container_pt'    => 'container',
			'duto'            => 'pipeline',
			'entrega_mao'     => 'hand_delivery',
			'retirada'        => 'pickup',
			'dropshipping'    => 'drop_shipping',
			'cruzamento_docas' => 'cross_docking',
			'multimodal_pt'   => 'multimodal',
			'intermodal_pt'   => 'intermodal',
			'outro'           => 'other',

			// Spanish aliases
			'aereo_es'        => 'air',
			'maritimo_es'     => 'sea',
			'terrestre_es'    => 'land',
			'carretera'       => 'road',
			'ferrocarril'     => 'rail',
			'mensajero'       => 'courier',
			'correo_es'       => 'postal',
			'expreso'         => 'express',
			'estandar'        => 'standard',
			'urgente_es'      => 'expedited',
			'mismo_dia'       => 'same_day',
			'siguiente_dia'   => 'next_day',
			'carga_es'        => 'freight',
			'camion'          => 'truck',
			'barco'           => 'ship',
			'carga_aerea_es'  => 'air_freight',
			'carga_maritima_es' => 'sea_freight',
			'paquete'         => 'parcel',
			'palet'           => 'pallet',
			'contenedor'      => 'container',
			'tuberia'         => 'pipeline',
			'entrega_manual'  => 'hand_delivery',
			'recogida'        => 'pickup',
			'dropshipping_es' => 'drop_shipping',
			'cruce_muelle'    => 'cross_docking',
			'multimodal_es'   => 'multimodal',
			'intermodal_es'   => 'intermodal',
			'otro'            => 'other',

			// French aliases
			'aerien'          => 'air',
			'maritime'        => 'sea',
			'terrestre_fr'    => 'land',
			'routier'         => 'road',
			'ferroviaire'     => 'rail',
			'messagerie'      => 'courier',
			'poste'           => 'postal',
			'express_fr'      => 'express',
			'standard_fr'     => 'standard',
			'accelere'        => 'expedited',
			'meme_jour'       => 'same_day',
			'lendemain'       => 'next_day',
			'fret_fr'         => 'freight',
			'camion_fr'       => 'truck',
			'bateau'          => 'ship',
			'fret_aerien'     => 'air_freight',
			'fret_maritime'   => 'sea_freight',
			'colis'           => 'parcel',
			'palette'         => 'pallet',
			'conteneur'       => 'container',
			'pipelines'       => 'pipeline',
			'livraison_main'  => 'hand_delivery',
			'ramassage'       => 'pickup',
			'dropshipping_fr' => 'drop_shipping',
			'cross_docking_fr' => 'cross_docking',
			'multimodal_fr'   => 'multimodal',
			'intermodal_fr'   => 'intermodal',
			'autre'           => 'other',
		];

		$k = $aliases[$k] ?? $k;

		return self::tryFrom($k) ?? self::Undefined;
	}

	public static function getIndex(?string $case): int
	{
		return match ($case) {
			self::Air->value          => 0,
			self::Sea->value          => 1,
			self::Land->value         => 2,
			self::Road->value         => 3,
			self::Rail->value         => 4,
			self::Courier->value      => 5,
			self::Postal->value       => 6,
			self::Express->value      => 7,
			self::Standard->value     => 8,
			self::Expedited->value    => 9,
			self::SameDay->value      => 10,
			self::NextDay->value      => 11,
			self::Freight->value      => 12,
			self::Truck->value        => 13,
			self::Ship->value         => 14,
			self::AirFreight->value   => 15,
			self::SeaFreight->value   => 16,
			self::LCL->value          => 17,
			self::FCL->value          => 18,
			self::Parcel->value       => 19,
			self::Pallet->value       => 20,
			self::Container->value    => 21,
			self::Pipeline->value     => 22,
			self::HandDelivery->value => 23,
			self::Pickup->value       => 24,
			self::DropShipping->value => 25,
			self::CrossDocking->value => 26,
			self::Multimodal->value   => 27,
			self::Intermodal->value   => 28,
			self::Other->value        => 29,
			self::Undefined->value    => 30,
			default => 30
		};
	}

	public static function getAllIndexes(): array
	{
		return array_values(
			array_map(fn($value) => self::getIndex($value), self::values())
		);
	}

	public static function values(): array
	{
		return [
			self::Air->value,
			self::Sea->value,
			self::Land->value,
			self::Road->value,
			self::Rail->value,
			self::Courier->value,
			self::Postal->value,
			self::Express->value,
			self::Standard->value,
			self::Expedited->value,
			self::SameDay->value,
			self::NextDay->value,
			self::Freight->value,
			self::Truck->value,
			self::Ship->value,
			self::AirFreight->value,
			self::SeaFreight->value,
			self::LCL->value,
			self::FCL->value,
			self::Parcel->value,
			self::Pallet->value,
			self::Container->value,
			self::Pipeline->value,
			self::HandDelivery->value,
			self::Pickup->value,
			self::DropShipping->value,
			self::CrossDocking->value,
			self::Multimodal->value,
			self::Intermodal->value,
			self::Other->value,
			self::Undefined->value,
		];
	}

	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::labelsPtBr(),
			'es', 'es-es' => self::labelsEs(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'he', 'he-il' => self::labelsHe(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'nl', 'nl-nl' => self::labelsNl(),
			'pl', 'pl-pl' => self::labelsPl(),
			'ru', 'ru-ru' => self::labelsRu(),
			'tr', 'tr-tr' => self::labelsTr(),
			'zh', 'zh-cn' => self::labelsZh(),
			default => self::labelsEn(),
		};
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Air->value          => 'Aéreo',
			self::Sea->value          => 'Marítimo',
			self::Land->value         => 'Terrestre',
			self::Road->value         => 'Rodoviário',
			self::Rail->value         => 'Ferroviário',
			self::Courier->value      => 'Correio Expresso',
			self::Postal->value       => 'Correio Postal',
			self::Express->value      => 'Expresso',
			self::Standard->value     => 'Padrão',
			self::Expedited->value    => 'Acelerado',
			self::SameDay->value      => 'Mesmo Dia',
			self::NextDay->value      => 'Próximo Dia',
			self::Freight->value      => 'Frete',
			self::Truck->value        => 'Caminhão',
			self::Ship->value         => 'Navio',
			self::AirFreight->value   => 'Carga Aérea',
			self::SeaFreight->value   => 'Carga Marítima',
			self::LCL->value          => 'Carga LCL',
			self::FCL->value          => 'Carga FCL',
			self::Parcel->value       => 'Pacote',
			self::Pallet->value       => 'Palete',
			self::Container->value    => 'Container',
			self::Pipeline->value     => 'Duto',
			self::HandDelivery->value => 'Entrega Manual',
			self::Pickup->value       => 'Retirada',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Outro',
			self::Undefined->value    => 'Indefinido',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Air->value          => 'Air',
			self::Sea->value          => 'Sea',
			self::Land->value         => 'Land',
			self::Road->value         => 'Road',
			self::Rail->value         => 'Rail',
			self::Courier->value      => 'Courier',
			self::Postal->value       => 'Postal',
			self::Express->value      => 'Express',
			self::Standard->value     => 'Standard',
			self::Expedited->value    => 'Expedited',
			self::SameDay->value      => 'Same Day',
			self::NextDay->value      => 'Next Day',
			self::Freight->value      => 'Freight',
			self::Truck->value        => 'Truck',
			self::Ship->value         => 'Ship',
			self::AirFreight->value   => 'Air Freight',
			self::SeaFreight->value   => 'Sea Freight',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Parcel',
			self::Pallet->value       => 'Pallet',
			self::Container->value    => 'Container',
			self::Pipeline->value     => 'Pipeline',
			self::HandDelivery->value => 'Hand Delivery',
			self::Pickup->value       => 'Pickup',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Other',
			self::Undefined->value    => 'Undefined',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Air->value          => 'Aéreo',
			self::Sea->value          => 'Marítimo',
			self::Land->value         => 'Terrestre',
			self::Road->value         => 'Carretera',
			self::Rail->value         => 'Ferrocarril',
			self::Courier->value      => 'Mensajero',
			self::Postal->value       => 'Correo',
			self::Express->value      => 'Expreso',
			self::Standard->value     => 'Estándar',
			self::Expedited->value    => 'Urgente',
			self::SameDay->value      => 'Mismo Día',
			self::NextDay->value      => 'Siguiente Día',
			self::Freight->value      => 'Carga',
			self::Truck->value        => 'Camión',
			self::Ship->value         => 'Barco',
			self::AirFreight->value   => 'Carga Aérea',
			self::SeaFreight->value   => 'Carga Marítima',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Paquete',
			self::Pallet->value       => 'Palet',
			self::Container->value    => 'Contenedor',
			self::Pipeline->value     => 'Tubería',
			self::HandDelivery->value => 'Entrega Manual',
			self::Pickup->value       => 'Recogida',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cruce de Muelle',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Otro',
			self::Undefined->value    => 'Indefinido',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Air->value          => 'جوي',
			self::Sea->value          => 'بحري',
			self::Land->value         => 'برّي',
			self::Road->value         => 'طريق',
			self::Rail->value         => 'سكك حديدية',
			self::Courier->value      => 'بريد سريع',
			self::Postal->value       => 'بريد',
			self::Express->value      => 'سريع',
			self::Standard->value     => 'قياسي',
			self::Expedited->value    => 'مستعجل',
			self::SameDay->value      => 'نفس اليوم',
			self::NextDay->value      => 'اليوم التالي',
			self::Freight->value      => 'شحن',
			self::Truck->value        => 'شاحنة',
			self::Ship->value         => 'سفينة',
			self::AirFreight->value   => 'شحن جوي',
			self::SeaFreight->value   => 'شحن بحري',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'طرد',
			self::Pallet->value       => 'بليت',
			self::Container->value    => 'حاوية',
			self::Pipeline->value     => 'خط أنابيب',
			self::HandDelivery->value => 'تسليم يدوي',
			self::Pickup->value       => 'استلام',
			self::DropShipping->value => 'إسقاط الشحن',
			self::CrossDocking->value => 'عبور الرصيف',
			self::Multimodal->value   => 'متعدد الوسائط',
			self::Intermodal->value   => 'بين الوسائط',
			self::Other->value        => 'أخرى',
			self::Undefined->value    => 'غير محدد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Air->value          => 'Luft',
			self::Sea->value          => 'Hav',
			self::Land->value         => 'Land',
			self::Road->value         => 'Vej',
			self::Rail->value         => 'Jernbane',
			self::Courier->value      => 'Kurér',
			self::Postal->value       => 'Post',
			self::Express->value      => 'Express',
			self::Standard->value     => 'Standard',
			self::Expedited->value    => 'Hurtig',
			self::SameDay->value      => 'Samme Dag',
			self::NextDay->value      => 'Næste Dag',
			self::Freight->value      => 'Fragt',
			self::Truck->value        => 'Lastbil',
			self::Ship->value         => 'Skib',
			self::AirFreight->value   => 'Luftfragt',
			self::SeaFreight->value   => 'Søfragt',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Pakke',
			self::Pallet->value       => 'Pallet',
			self::Container->value    => 'Container',
			self::Pipeline->value     => 'Rørledning',
			self::HandDelivery->value => 'Håndlevering',
			self::Pickup->value       => 'Afhentning',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Andet',
			self::Undefined->value    => 'Udefineret',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Air->value          => 'Luft',
			self::Sea->value          => 'See',
			self::Land->value         => 'Land',
			self::Road->value         => 'Straße',
			self::Rail->value         => 'Schiene',
			self::Courier->value      => 'Kurier',
			self::Postal->value       => 'Post',
			self::Express->value      => 'Express',
			self::Standard->value     => 'Standard',
			self::Expedited->value    => 'Beschleunigt',
			self::SameDay->value      => 'Gleicher Tag',
			self::NextDay->value      => 'Nächster Tag',
			self::Freight->value      => 'Fracht',
			self::Truck->value        => 'LKW',
			self::Ship->value         => 'Schiff',
			self::AirFreight->value   => 'Luftfracht',
			self::SeaFreight->value   => 'Seefracht',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Paket',
			self::Pallet->value       => 'Palette',
			self::Container->value    => 'Container',
			self::Pipeline->value     => 'Pipeline',
			self::HandDelivery->value => 'Handzustellung',
			self::Pickup->value       => 'Abholung',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Andere',
			self::Undefined->value    => 'Undefiniert',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Air->value          => 'Aérien',
			self::Sea->value          => 'Maritime',
			self::Land->value         => 'Terrestre',
			self::Road->value         => 'Routier',
			self::Rail->value         => 'Ferroviaire',
			self::Courier->value      => 'Courrier',
			self::Postal->value       => 'Poste',
			self::Express->value      => 'Express',
			self::Standard->value     => 'Standard',
			self::Expedited->value    => 'Accéléré',
			self::SameDay->value      => 'Même Jour',
			self::NextDay->value      => 'Lendemain',
			self::Freight->value      => 'Fret',
			self::Truck->value        => 'Camion',
			self::Ship->value         => 'Bateau',
			self::AirFreight->value   => 'Fret Aérien',
			self::SeaFreight->value   => 'Fret Maritime',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Colis',
			self::Pallet->value       => 'Palette',
			self::Container->value    => 'Conteneur',
			self::Pipeline->value     => 'Pipeline',
			self::HandDelivery->value => 'Livraison Manuelle',
			self::Pickup->value       => 'Ramassage',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Autre',
			self::Undefined->value    => 'Indéfini',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Air->value          => 'אוויר',
			self::Sea->value          => 'ים',
			self::Land->value         => 'יבשה',
			self::Road->value         => 'כביש',
			self::Rail->value         => 'רכבת',
			self::Courier->value      => 'שליח',
			self::Postal->value       => 'דואר',
			self::Express->value      => 'אקספרס',
			self::Standard->value     => 'סטנדרטי',
			self::Expedited->value    => 'מואץ',
			self::SameDay->value      => 'אותו יום',
			self::NextDay->value      => 'יום למחרת',
			self::Freight->value      => 'מטען',
			self::Truck->value        => 'משאית',
			self::Ship->value         => 'ספינה',
			self::AirFreight->value   => 'מטען אוויר',
			self::SeaFreight->value   => 'מטען ים',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'חבילה',
			self::Pallet->value       => 'משטח',
			self::Container->value    => 'מיכל',
			self::Pipeline->value     => 'צינור',
			self::HandDelivery->value => 'מסירה ידנית',
			self::Pickup->value       => 'איסוף',
			self::DropShipping->value => 'דרופ שיפינג',
			self::CrossDocking->value => 'קרוס דוקינג',
			self::Multimodal->value   => 'רב-שיטי',
			self::Intermodal->value   => 'בין-שיטי',
			self::Other->value        => 'אחר',
			self::Undefined->value    => 'לא מוגדר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Air->value          => 'Aereo',
			self::Sea->value          => 'Mare',
			self::Land->value         => 'Terra',
			self::Road->value         => 'Strada',
			self::Rail->value         => 'Ferrovia',
			self::Courier->value      => 'Corriere',
			self::Postal->value       => 'Postale',
			self::Express->value      => 'Espresso',
			self::Standard->value     => 'Standard',
			self::Expedited->value    => 'Accelerato',
			self::SameDay->value      => 'Stesso Giorno',
			self::NextDay->value      => 'Giorno Successivo',
			self::Freight->value      => 'Trasporto',
			self::Truck->value        => 'Camion',
			self::Ship->value         => 'Nave',
			self::AirFreight->value   => 'Trasporto Aereo',
			self::SeaFreight->value   => 'Trasporto Marittimo',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Pacco',
			self::Pallet->value       => 'Pallet',
			self::Container->value    => 'Container',
			self::Pipeline->value     => 'Conduttura',
			self::HandDelivery->value => 'Consegna Manuale',
			self::Pickup->value       => 'Ritiro',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodale',
			self::Intermodal->value   => 'Intermodale',
			self::Other->value        => 'Altro',
			self::Undefined->value    => 'Indefinito',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Air->value          => '航空',
			self::Sea->value          => '海上',
			self::Land->value         => '陸上',
			self::Road->value         => '道路',
			self::Rail->value         => '鉄道',
			self::Courier->value      => '宅配便',
			self::Postal->value       => '郵便',
			self::Express->value      => '速達',
			self::Standard->value     => '標準',
			self::Expedited->value    => '速達便',
			self::SameDay->value      => '当日配送',
			self::NextDay->value      => '翌日配送',
			self::Freight->value      => '貨物',
			self::Truck->value        => 'トラック',
			self::Ship->value         => '船',
			self::AirFreight->value   => '航空貨物',
			self::SeaFreight->value   => '海上貨物',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => '小包',
			self::Pallet->value       => 'パレット',
			self::Container->value    => 'コンテナ',
			self::Pipeline->value     => 'パイプライン',
			self::HandDelivery->value => '手渡し',
			self::Pickup->value       => '受け取り',
			self::DropShipping->value => 'ドロップシッピング',
			self::CrossDocking->value => 'クロスドッキング',
			self::Multimodal->value   => '複合輸送',
			self::Intermodal->value   => 'インターコンテナル',
			self::Other->value        => 'その他',
			self::Undefined->value    => '未定義',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Air->value          => 'Lucht',
			self::Sea->value          => 'Zee',
			self::Land->value         => 'Land',
			self::Road->value         => 'Weg',
			self::Rail->value         => 'Spoor',
			self::Courier->value      => 'Koerier',
			self::Postal->value       => 'Post',
			self::Express->value      => 'Express',
			self::Standard->value     => 'Standaard',
			self::Expedited->value    => 'Versneld',
			self::SameDay->value      => 'Zelfde Dag',
			self::NextDay->value      => 'Volgende Dag',
			self::Freight->value      => 'Vracht',
			self::Truck->value        => 'Vrachtwagen',
			self::Ship->value         => 'Schip',
			self::AirFreight->value   => 'Luchtvracht',
			self::SeaFreight->value   => 'Zeevracht',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Pakket',
			self::Pallet->value       => 'Pallet',
			self::Container->value    => 'Container',
			self::Pipeline->value     => 'Pijpleiding',
			self::HandDelivery->value => 'Handaflevering',
			self::Pickup->value       => 'Ophalen',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodaal',
			self::Intermodal->value   => 'Intermodaal',
			self::Other->value        => 'Anders',
			self::Undefined->value    => 'Ongedefinieerd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Air->value          => 'Powietrze',
			self::Sea->value          => 'Morze',
			self::Land->value         => 'Ląd',
			self::Road->value         => 'Droga',
			self::Rail->value         => 'Kolej',
			self::Courier->value      => 'Kurier',
			self::Postal->value       => 'Poczta',
			self::Express->value      => 'Ekspres',
			self::Standard->value     => 'Standard',
			self::Expedited->value    => 'Przyspieszony',
			self::SameDay->value      => 'Tego Samego Dnia',
			self::NextDay->value      => 'Następny Dzień',
			self::Freight->value      => 'Fracht',
			self::Truck->value        => 'Ciężarówka',
			self::Ship->value         => 'Statek',
			self::AirFreight->value   => 'Fracht Lotniczy',
			self::SeaFreight->value   => 'Fracht Morski',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Przesyłka',
			self::Pallet->value       => 'Paleta',
			self::Container->value    => 'Kontener',
			self::Pipeline->value     => 'Rurociąg',
			self::HandDelivery->value => 'Dostawa Ręczna',
			self::Pickup->value       => 'Odbiór',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodalny',
			self::Intermodal->value   => 'Intermodalny',
			self::Other->value        => 'Inny',
			self::Undefined->value    => 'Niezdefiniowany',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Air->value          => 'Воздушный',
			self::Sea->value          => 'Морской',
			self::Land->value         => 'Наземный',
			self::Road->value         => 'Автомобильный',
			self::Rail->value         => 'Железнодорожный',
			self::Courier->value      => 'Курьер',
			self::Postal->value       => 'Почта',
			self::Express->value      => 'Экспресс',
			self::Standard->value     => 'Стандартный',
			self::Expedited->value    => 'Срочный',
			self::SameDay->value      => 'В Тот Же День',
			self::NextDay->value      => 'На Следующий День',
			self::Freight->value      => 'Груз',
			self::Truck->value        => 'Грузовик',
			self::Ship->value         => 'Корабль',
			self::AirFreight->value   => 'Авиаперевозки',
			self::SeaFreight->value   => 'Морские Перевозки',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Посылка',
			self::Pallet->value       => 'Паллета',
			self::Container->value    => 'Контейнер',
			self::Pipeline->value     => 'Трубопровод',
			self::HandDelivery->value => 'Ручная Доставка',
			self::Pickup->value       => 'Самовывоз',
			self::DropShipping->value => 'Дропшиппинг',
			self::CrossDocking->value => 'Кросс-докинг',
			self::Multimodal->value   => 'Мультимодальный',
			self::Intermodal->value   => 'Интермодальный',
			self::Other->value        => 'Другой',
			self::Undefined->value    => 'Неопределенный',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Air->value          => 'Hava',
			self::Sea->value          => 'Deniz',
			self::Land->value         => 'Kara',
			self::Road->value         => 'Karayolu',
			self::Rail->value         => 'Demiryolu',
			self::Courier->value      => 'Kurye',
			self::Postal->value       => 'Posta',
			self::Express->value      => 'Ekspres',
			self::Standard->value     => 'Standart',
			self::Expedited->value    => 'Acil',
			self::SameDay->value      => 'Aynı Gün',
			self::NextDay->value      => 'Ertesi Gün',
			self::Freight->value      => 'Kargo',
			self::Truck->value        => 'Kamyon',
			self::Ship->value         => 'Gemi',
			self::AirFreight->value   => 'Hava Kargo',
			self::SeaFreight->value   => 'Deniz Kargo',
			self::LCL->value          => 'LCL',
			self::FCL->value          => 'FCL',
			self::Parcel->value       => 'Paket',
			self::Pallet->value       => 'Palet',
			self::Container->value    => 'Konteyner',
			self::Pipeline->value     => 'Boru Hattı',
			self::HandDelivery->value => 'Elden Teslim',
			self::Pickup->value       => 'Alım',
			self::DropShipping->value => 'Drop Shipping',
			self::CrossDocking->value => 'Cross Docking',
			self::Multimodal->value   => 'Multimodal',
			self::Intermodal->value   => 'Intermodal',
			self::Other->value        => 'Diğer',
			self::Undefined->value    => 'Tanımsız',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Air->value          => '航空',
			self::Sea->value          => '海运',
			self::Land->value         => '陆运',
			self::Road->value         => '公路',
			self::Rail->value         => '铁路',
			self::Courier->value      => '快递',
			self::Postal->value       => '邮政',
			self::Express->value      => '特快',
			self::Standard->value     => '标准',
			self::Expedited->value    => '加急',
			self::SameDay->value      => '当日达',
			self::NextDay->value      => '次日达',
			self::Freight->value      => '货运',
			self::Truck->value        => '卡车',
			self::Ship->value         => '船舶',
			self::AirFreight->value   => '空运',
			self::SeaFreight->value   => '海运货运',
			self::LCL->value          => '拼箱',
			self::FCL->value          => '整箱',
			self::Parcel->value       => '包裹',
			self::Pallet->value       => '托盘',
			self::Container->value    => '集装箱',
			self::Pipeline->value     => '管道',
			self::HandDelivery->value => '人工交付',
			self::Pickup->value       => '自提',
			self::DropShipping->value => '代发货',
			self::CrossDocking->value => '交叉转运',
			self::Multimodal->value   => '多式联运',
			self::Intermodal->value   => '联运',
			self::Other->value        => '其他',
			self::Undefined->value    => '未定义',
		];
	}
}
