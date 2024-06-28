import {__} from '@wordpress/i18n';

export const attributes = {
  accept: {
    type: 'boolean',
    default: true,
  },
  acceptLabel: {
    type: 'text',
    default: 'Accept',
  },
  rejectLabel: {
    type: 'text',
    default: 'Reject',
  },
  textAlign: {
    type: 'string',
    default: 'left',
  },
  fontSize: {
    type: 'string',
    default: 'small',
  },
  buttonTextTransform: {
    type: 'string',
    default: 'none',
  },
  buttonColor: {
    type: 'string',
    default: '#000000',
  },
  buttonBackgroundColor: {
    type: 'string',
    default: '#ffffff',
  },
  buttonBorderRadius: {
    type: 'number',
    default : 0
  },

};
