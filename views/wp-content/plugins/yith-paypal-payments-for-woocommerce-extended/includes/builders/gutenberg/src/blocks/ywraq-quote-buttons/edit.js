import {__} from '@wordpress/i18n';
import {
	RichText,
	InspectorControls,
	AlignmentControl,
	BlockControls,
	useBlockProps,
	__experimentalPanelColorGradientSettings as PanelColorGradientSettings,
	__experimentalTextTransformControl as  TransformControl,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	ButtonGroup,
	FontSizePicker,
	RangeControl
} from "@wordpress/components";
import {fontSizes} from '../../packages/constants';



function EditorQuoteButtons(props) {
	const {attributes, setAttributes, style} = props;

	const {
		accept,
		acceptLabel,
		rejectLabel,
		textAlign,
		fontSize,
		buttonColor,
		buttonBackgroundColor,
		buttonTextTransform,
		buttonBorderRadius
	} = attributes;

	const wrapperClass = `ywraq-wrapper-buttons has-text-align-${textAlign}`;
	const blockProps = useBlockProps({
		style: {textAlign:textAlign, color:buttonColor, textTransform: buttonTextTransform, backgroundColor:buttonBackgroundColor, fontSize:fontSize,
		borderRadius:buttonBorderRadius+'px'},
	});



	return (
		<>
			<BlockControls group="block">
				<AlignmentControl
					value={textAlign}
					onChange={(nextAlign) => {
						setAttributes({textAlign: nextAlign});
					}}
				/>
			</BlockControls>
			<InspectorControls>
				<PanelBody
					title={__('Quote Buttons settings', 'yith-woocommerce-request-a-quote')}
					className="blocks-quote-buttons-settings"
				>
					<ButtonGroup>
						<Button variant={accept ? "primary":"isSecondary"} onClick={() => setAttributes({accept: true})}>{__('Accept quote', 'yith-woocommerce-request-a-quote')}</Button>
						<Button variant={accept ? "isSecondary":"primary"} onClick={() => setAttributes({accept: false})}>{__('Reject quote', 'yith-woocommerce-request-a-quote')}</Button>
					</ButtonGroup>
				</PanelBody>
				<PanelColorGradientSettings
						title={__('Button settings', 'yith-woocommerce-request-a-quote')}
						initialOpen={true}
						settings={[
							{
								label: __('Color', 'yith-woocommerce-request-a-quote'),
								onColorChange: (color) => setAttributes({buttonColor:color}),
								colorValue: buttonColor,
							},
							{
								label: __('Background color', 'yith-woocommerce-request-a-quote'),
								onColorChange: (color) => setAttributes({buttonBackgroundColor:color}),
								colorValue: buttonBackgroundColor,
							},
						]}
				>
					<RangeControl
							label={__('Border radius', 'yith-woocommerce-request-a-quote')}
							step={1}
							withInputField={true}
							value={buttonBorderRadius }
							onChange={ (buttonBorderRadius) => {
								setAttributes({buttonBorderRadius}); } }
							min={ 0 }
							max={ 50 }
					/>
					<FontSizePicker
							fontSizes={fontSizes}
							value={fontSize || 20}
							fallbackFontSize={20}
							withSlider={false}
							onChange={(newFontSize) => {
								setAttributes({fontSize: newFontSize});
							}}
					/>
					<TransformControl
							value={buttonTextTransform}
							onChange={(textTransform) => {
								setAttributes({buttonTextTransform: textTransform});
							}}
					/>

				</PanelColorGradientSettings>
			</InspectorControls>
			<div className={wrapperClass} >
				{accept && (
						<RichText
								tagName={'p'}
								value={acceptLabel}
								className='accept-quote'
								onChange={(value) => setAttributes({acceptLabel: value})}
								{...blockProps}
						/>)}

				{!accept && (
						<RichText
								tagName={'p'}
								value={rejectLabel}
								className='reject-quote'
								onChange={(value) => setAttributes({rejectLabel: value})}
								{...blockProps}
						/>)}
			</div>
		</>
	);
}

export default EditorQuoteButtons;