/* Contact: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var ICONS = [
    { label: 'Email', value: 'mail' }, { label: 'WhatsApp / chat', value: 'chat' }, { label: 'Clock', value: 'clock' },
    { label: 'Location', value: 'pin' }, { label: 'Info', value: 'help' }
  ];

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('email-alt', 'Contact', 'Contact cards and a form that opens the visitor’s email app.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: false },
        L.Text('Heading', a.heading, function (v) { setA('heading', v); }),
        L.Text('Text under the heading', a.intro, function (v) { setA('intro', v); })
      ),
      el(L.Repeater, { items: a.cards, onChange: function (v) { setA('cards', v); }, itemLabel: 'Card', itemTitle: function (x) { return x.title; }, blank: { icon: 'help', title: '', text: '', value: '', url: '' }, addLabel: '+ Add card',
        render: function (x, set) {
          return [
            L.Row([L.Text('Title', x.title, function (v) { set('title', v); }), el(c.SelectControl, { label: 'Icon', value: x.icon, options: ICONS, onChange: function (v) { set('icon', v); } })]),
            L.Text('Short text', x.text, function (v) { set('text', v); }),
            L.Row([L.Text('Bold line (email, number, hours…)', x.value, function (v) { set('value', v); }), L.Text('Link (empty = no link; /faq/ for a page)', x.url, function (v) { set('url', v); })])
          ];
        } }),
      el(c.PanelBody, { title: 'Message form', initialOpen: false },
        L.Text('Title', a.formTitle, function (v) { setA('formTitle', v); }),
        L.Text('Text', a.formText, function (v) { setA('formText', v); }),
        L.Text('Messages are sent to this email', a.email, function (v) { setA('email', v); }),
        L.Area('“What is it about?” choices, one per line', a.topics, function (v) { setA('topics', v); }, { rows: 5 })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/contact', { edit: Edit, save: function () { return null; } });
})(window.wp);
