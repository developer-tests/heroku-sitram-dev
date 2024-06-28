import React from "react";
import ReactDOM from "react-dom";
import TemplateList from './template-list';

wp.domReady(
	ReactDOM.render(
		<TemplateList />,
		document.getElementById("ywraq_pdf_templates")
	)
);