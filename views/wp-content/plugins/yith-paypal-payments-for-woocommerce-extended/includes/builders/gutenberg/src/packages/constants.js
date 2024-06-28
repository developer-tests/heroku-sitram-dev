import { applyFilters } from '@wordpress/hooks';
const {__} = wp.i18n;
/** global ywraq_pdf_template */
export const YWRAQ_PREVIEW_IMAGE_URL = ywraq_pdf_template.preview_image_url;
export const YWRAQ_LICENCE = applyFilters( 'ywraq_pdf_template_demo', ywraq_pdf_template.licence_key );
export const YWRAQ_LICENCE_URL = ywraq_pdf_template.licence_url;
export const templateURL = {
  'baseURL':'https://plugins.yithemes.com/resources/',
  'folder': ywraq_pdf_template.slug + '/pdf-templates/',
  'preview':'preview/',
  'content':'content/',
  'images' : 'images/'
      };
export const templates = ywraq_pdf_template.templates;
export const previewQuote = ywraq_pdf_template.preview_products;

export const fontSizes = [
  {
    name: __('Small'),
    slug: 'small',
    size: 13,
  },
  {
    name: __('Medium'),
    slug: 'medium',
    size: 20,
  },
  {
    name: __('Large'),
    slug: 'large',
    size: 36,
  },
  {
    name: __('Extra Large'),
    slug: 'extralarge',
    size: 42,
  },
];