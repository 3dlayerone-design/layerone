/* Drop list signup: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('email-alt', 'Drop list signup', 'Emails are saved under Tools > Drop list.'),
      el('div', { style: L.CARD },
        L.Row([L.Text('Heading', a.heading, function (v) { setA('heading', v); }), L.Text('Heading, italic part', a.headingAccent, function (v) { setA('headingAccent', v); })]),
        L.Text('Text after the italic part (optional)', a.tail, function (v) { setA('tail', v); }),
        L.Row([L.Text('Email field hint', a.placeholder, function (v) { setA('placeholder', v); }), L.Text('Button text', a.button, function (v) { setA('button', v); })]),
        L.Text('Thank-you message', a.thanks, function (v) { setA('thanks', v); })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/newsletter', { edit: Edit, save: function () { return null; } });
})(window.wp);
