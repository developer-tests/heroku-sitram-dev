<tr valign="top" id="service_options" class="rates_tab_field">
	<td class="forminp" colspan="2" style="padding-left:0px">
	<strong><?php esc_attr( 'Services', 'wf-easypost' ); ?></strong><br/>
		<table class="easypost_services widefat">
			<thead>
				<th style="padding-left:10px"><?php echo esc_attr( 'Service(s)', 'wf-easypost' ); ?></th>
				<th><?php echo esc_attr( 'Name', 'wf-easypost' ); ?></th>
				<th><?php echo sprintf( esc_attr( 'Price Adjustment (' . get_woocommerce_currency_symbol() . ')', 'wf-easypost' ) ); ?></th>
				<th><?php echo esc_attr( 'Price Adjustment (%)', 'wf-easypost' ); ?></th>
			</thead>
			<tbody>
				<?php
					$sort                   = 0;
					$this->ordered_services = array();
					$rates_settings         = get_option( 'woocommerce_WF_EASYPOST_ID_rates_settings', null );
					$custom_services        = isset( $rates_settings['services'] ) ? $rates_settings['services'] : array();
				foreach ( $this->services as $code => $values ) {
					$ordered_services = array();
					foreach ( $values['services'] as $key => $value ) {
						if ( is_array( $custom_services ) && isset( $custom_services[ $code ][ $key ]['order'] ) && ! empty( $custom_services[ $code ][ $key ]['order'] ) ) {
							$sort = $custom_services[ $code ] [ $key ] ['order'];
						}

						while ( isset( $this->ordered_services[ $sort ] ) ) {
							$sort++;
						}

						if ( ! empty( $custom_services ) && array_key_exists( $code, $custom_services ) ) {
							$ordered_services[ $sort ] = array( $key, $custom_services[ $code ][ $key ] );
						} else {
							$ordered_services[ $sort ] = array(
								$key,
								array(
									$code => array(
										$key => array(
											'enalbled'   => true,
											'adjustment' => '',
											'adjustment_percent' => '',
											'name'       => '',
											'order'      => '',
										),
									),
								),
							);
						}

						$sort++;
					}
						// }
						$this->ordered_services[ $code ] = $ordered_services;
				}


				foreach ( $this->ordered_services as $key => $value ) {
					ksort( $this->ordered_services[ $key ] );
				}
				foreach ( $this->ordered_services as $code => $value ) {
					if ( ! empty( $custom_services ) && ! isset( $custom_services[ $code ] ) ) {
							$custom_services[ $code ] = array();
					}

					foreach ( $value as $orders => $values ) {
						$key = $values[0];

						?>
							<tr class="services" carrier="<?php echo esc_attr( $code ); ?>">
							
								<td style="padding-left:10px">
									<label>
										<input type="checkbox" name="easypost_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][enabled]" <?php checked( ( ! isset( $custom_services[ $code ][ $key ]['enabled'] ) || ! empty( $custom_services[ $code ][ $key ]['enabled'] ) ), true ); ?> />
									<?php echo esc_attr( $key ); ?>
									</label>
								</td>
								<td>	
									<input type="text" name="easypost_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][name]" placeholder="<?php echo esc_attr( (string) $this->services[ $code ]['services'][ $key ] ); ?>" value="<?php echo isset( $custom_services[ $code ][ $key ]['name'] ) ? esc_attr( $custom_services[ $code ][ $key ]['name'] ) : ''; ?>" size="30" />
								</td>
								<td>
								<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?><input type="text" name="easypost_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][adjustment]" placeholder="N/A" value="<?php echo isset( $custom_services[ $code ][ $key ]['adjustment'] ) ? esc_attr( $custom_services[ $code ][ $key ]['adjustment'] ) : ''; ?>" size="4" />
								</td>
								<td>
									<input type="text" name="easypost_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $custom_services[ $code ][ $key ]['adjustment_percent'] ) ? esc_attr( $custom_services[ $code ][ $key ]['adjustment_percent'] ) : ''; ?>" size="4" />%
								</td>
							</tr>
							<?php
					}
				}
				?>
			</tbody>
		</table>
	</td>
</tr>
