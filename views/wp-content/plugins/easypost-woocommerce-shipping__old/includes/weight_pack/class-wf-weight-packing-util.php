<?php
if ( ! class_exists( 'WeightPacketUtil' ) ) {
	class WeightPacketUtil {
		public function pack_items_into_weight_box( $items, $max_weight ) {
			$boxes    = array();
			$unpacked = array();
			$value    = 0;
			$i        = 0;
			foreach ( $items as $item ) {
				$item_data   = $item['data'];
				$fitted      = false;
				$item_weight = $item['weight'];
				foreach ( $boxes as $box_key  => $box ) {
					if ( ( $max_weight - $box['weight'] ) >= $item_weight ) {
						$boxes[ $box_key ]['weight']      = $boxes[ $box_key ]['weight'] + $item_weight;
						$boxes[ $box_key ]['items'][ $i ] = $item['data'];
						// $value += $item['data']->get_price();
						$fitted = true;
						$i++;

					}
				}
				if ( ! $fitted ) {
					if ( $item_weight <= $max_weight ) {
						$boxes[] = array(
							'weight' => $item_weight,
							'items'  => array( $i => $item['data'] ),
						);
						$i++;

					} else {
						$unpacked[] = array(
							'weight' => $item_weight,
							'items'  => array( $i => $item['data'] ),
						);
						$i++;
					}
				}
			}
			$result = new WeightPackResult();
			$result->set_packed_boxes( $boxes );
			$result->set_unpacked_items( $unpacked );
			return $result;
		}

		public function pack_all_items_into_one_box( $items ) {
			$boxes        = array();
			$total_weight = 0;
			$i            = 0;
			$box_items    = array();
			foreach ( $items as $item ) {
				$item_data       = $item['data'];
				$total_weight    = $total_weight + $item['weight'];
				$box_items[ $i ] = $item['data'];
				$i++;
			}
			$boxes[] = array(
				'weight' => $total_weight,
				'items'  => $box_items,
			);
			$result  = new WeightPackResult();
			$result->set_packed_boxes( $boxes );
			return $result;
		}
	}
}
