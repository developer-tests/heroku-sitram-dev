import {__, _x} from '@wordpress/i18n';

wp.domReady(() => {
  const {
    data: { useSelect, useDispatch },
    plugins: { registerPlugin },
    element: { useState, useEffect },
    components: { TextareaControl },
    editPost: { PluginDocumentSettingPanel },
  } = wp;

  /**
   * Sidebar metabox.
   */
  const FooterDataSettings = () => {
    const {
      meta,
      meta: { _footer_content },
    } = useSelect((select) => ({
      meta: select('core/editor').getEditedPostAttribute('meta') || {},
    }));

    const { editPost } = useDispatch('core/editor');
    const [footer, setFooter] = useState(_footer_content);

    useEffect(() => {
      editPost({
        meta: {
          ...meta,
          _footer_content: footer,
        },
      });
    }, [footer]);

    return (
        <PluginDocumentSettingPanel name="footer-data" title={_x('Footer','Builder pdf template - Label Footer option','yith-woocommerce-request-a-quote')}>
          <TextareaControl value={footer} onChange={setFooter} />
          <small>{_x('Use {PAGENO} to show the number of the page.','Builder pdf template - Description Footer option', 'yith-woocommerce-request-a-quote')}</small>
        </PluginDocumentSettingPanel>
    );
  };

  registerPlugin( 'document-setting-test', { render: FooterDataSettings , icon: null,} );
});