import React, {useState, useEffect } from 'react';
import {defaultTemplate} from '../data/templates.js';
import {__} from '@wordpress/i18n';
import {
    YWRAQ_LICENCE,
    YWRAQ_LICENCE_URL,
    templateURL,
    YWRAQ_PREVIEW_IMAGE_URL,
} from '../../packages/constants';

function TemplateList() {

    let [remoteTemplates, setRemoteTemplates] = useState([]);
    let [templateSelected, setTemplateSelected] = useState('');
    let [savedTemplateSelected, setSavedTemplateSelected] = useState('');
    const [loading, setLoading] = useState(true);

    const imageLoaded = (index, total) => {
        if (loading && index === total - 1) {
            setLoading(false);
        }
    };

    const getRemoteTemplates = () => {
        fetch(ywraq_pdf_template.ajaxurl, {
            method: 'POST',
            headers: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            body: formatRequestBody([
                {name: 'action', value: 'ywraq_get_pdf_templates'},
                {name: 'security', value: ywraq_pdf_template.get_templates_security},
            ])
        }).then((response) => {
            return response.json();
        }).then((templates) => {
            if (typeof templates.templates !== 'undefined') {
                setRemoteTemplates(templates.templates);
                /*templates.templates.map((t, index) => {
                        if (t.id === savedTemplateSelected) {
                            addBG(t);
                        }
                    },
                );*/
            }

        }).catch((error) => {
            console.error(error);
        });
    };

    function getTemplateSelected() {
        const currentPost = wp.data.select('core/editor').getCurrentPost();

        if (currentPost) {
      if (templateSelected === '' && currentPost.status === 'auto-draft' && currentPost.content === '') {
        setSavedTemplateSelected( 'default' );
                reloadEditor(defaultTemplate);
      }else if(currentPost.content !== '' && typeof currentPost.meta !== 'undefined') {
        setSavedTemplateSelected( currentPost.meta['_template_parent'] );
            }
        }else{
      setSavedTemplateSelected( 'default' );
            reloadEditor(defaultTemplate);
        }

    }

    // Format the request body.
    function formatRequestBody(body) {
        var formatted = [];

        jQuery.each(body, function (index, item) {
            formatted.push(item.name + '=' + item.value);
        });

        return formatted.join('&');
    }

    const title = document.getElementById('ywraq_pdf_template_title');
    title.addEventListener('change', function () {
        wp.data.dispatch('core/editor').editPost({title: title.value});
    });


    const { select, subscribe } = wp.data;

    const closeListener = subscribe( () => {
        const isReady = select( 'core/editor' ).getCurrentPost();
        if ( ! isReady || typeof isReady.status === 'undefined') {
            // Editor not ready.
            return;
        }

        // Close the listener as soon as we know we are ready to avoid an infinite loop.
        closeListener();
        // Your code is placed after this comment, once the editor is ready.
        getTemplateSelected();
    });

    useEffect(() => {
        remoteTemplates.length === 0 && getRemoteTemplates();
    }, []);

    function handleOnClick(template, index = -1) {
        setTemplateSelected(template);
        if (template.content) {
            reloadEditor(template);
        } else {
            let currentRemoteTemplate = remoteTemplates[index];
            fetch(ywraq_pdf_template.ajaxurl, {
                method: 'POST',
                headers: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                body: formatRequestBody([
                    {name: 'action', value: 'ywraq_get_template_pdf_content'},
                    {name: 'template_id', value: template.id},
                    {name: 'security', value: ywraq_pdf_template.get_template_content_security},
                ])
            }).then(function (res) {
                return res.json();
            }).then(text => {
                if (typeof text.content !== "undefined") {
                    currentRemoteTemplate.content = text.content;
                    remoteTemplates[index] = currentRemoteTemplate;
                    setRemoteTemplates(remoteTemplates);
                    reloadEditor(currentRemoteTemplate);
                }
            }).catch((error) => {
                console.error(error);
            });
        }

    }

    function reloadEditor(template) {
    const meta=  wp.data.select('core/editor').getEditedPostAttribute('meta');
    wp.data.dispatch('core/editor').editPost({content: template.content, meta: {
        ...meta,
        _template_parent: template.id
      }});
        wp.data.dispatch('core/block-editor').resetBlocks(wp.blocks.parse(template.content));
    }

    const imgPreviewUrl = templateURL['baseURL'] + templateURL['folder'] +
        templateURL['preview'];
    const t = '' === templateSelected ?
        savedTemplateSelected :
        templateSelected.id;

    addBG();

    function addBG(template = false) {

        if (!templateSelected && !template && !savedTemplateSelected) {
            return;
        }

        if (templateSelected) {
            template = templateSelected;
        } else if (savedTemplateSelected) {
            remoteTemplates.map(t => {
                    if (t.id === savedTemplateSelected) {
                        template = t;
                    }
                }
            );
        }

        let bg = false;
        if (typeof template.background !== 'undefined ') {
            bg = templateURL['baseURL'] + templateURL['folder'] +
                templateURL['images'] + template.background;
        }

        const elements = document.querySelectorAll('.editor-styles-wrapper');
        for (var i = 0; i < elements.length; i++) {
            if (bg) {
                elements[i].style.background = 'url(' + bg + ') no-repeat 0 0';
            }
            let newClasses = [];
            let classNames = elements[i].className.split(' ');
            for (let i = 0; i < classNames.length; i++) {
                if (!classNames[i].includes('ywraq-template-')) {
                    newClasses = [...newClasses, classNames[i]];
                }
            }
            newClasses = [...newClasses, 'ywraq-template-' + template.id];
            elements[i].className = newClasses.join(' ');

        }

    }

    return (
        <>
            <div className={'default-templates-wrapper visible'}>
                <div className={`template-item ${(t ===
                    defaultTemplate.id) ? 'selected' : ''}`}
                     onClick={() => handleOnClick(defaultTemplate)}>
                    <img className={'template-preview'}
                         src={YWRAQ_PREVIEW_IMAGE_URL + '/' + defaultTemplate.preview}/>
                </div>
            </div>

            {!YWRAQ_LICENCE && remoteTemplates.length > 0 && (
                <div className={`remote-templates-wrapper no-licence ${(loading ?
                    '' :
                    'visible')}`}>
                    <a href={YWRAQ_LICENCE_URL} >
                    <div className={'no-licence-message'}>
                        <span className=" yith-icon yith-icon-lock"></span>
                        <span className={'no-licence-message__action'}>{__(
                            'Enter your licence key',
                            'yith-woocommerce-request-a-quote')}</span>
                        <span className={'no-licence-message__text'}>{__(
                            'to unlock 8 quote templates with different designs and styles',
                            'yith-woocommerce-request-a-quote')}</span>
                    </div>
                    {remoteTemplates.map((template, index) => (
                        <div className={`template-item  ${(t ===
                            template.id) ? 'selected' : ''}`} key={template.id}>
                            <img className={'template-preview'}
                                 src={imgPreviewUrl + template.preview}
                                 onLoad={imageLoaded(index, remoteTemplates.length)}/>
                        </div>
                    ))}
                    </a>
                </div>
            )}
            {YWRAQ_LICENCE && remoteTemplates.length > 0 && (
                <div className={`remote-templates-wrapper with-licence ${(loading ?
                    '' :
                    'visible')}`}>
                    {remoteTemplates.map((template, index) => (
                        <div className={`template-item ${(t ===
                            template.id) ? 'selected' : ''} `} key={template.id}
                             onClick={() => handleOnClick(template, index)}>
                            <img className={'template-preview'}
                                 src={imgPreviewUrl + template.preview}
                                 onLoad={imageLoaded(index, remoteTemplates.length)}/>
                        </div>
                    ))}
                </div>
            )
            }

        </>

    );
}

export default TemplateList;

