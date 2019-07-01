(function (blocks, i18n, element, components) {
    var SelectControl = components.SelectControl;
    var blockStyle = { padding: '1px' };

    var el = element.createElement; // The wp.element.createElement() function to create elements.

    blocks.registerBlockType('simple-event-calendar/index', {
        title: 'GrandWP Calendar',

        icon: 'calendar-alt',

        category: 'simple-event-calendar',

        attributes: {
            calendar_id: {
                type: 'string'
            }
        },

        edit: function (props) {


            var focus = props.focus;

            props.attributes.calendar_id =  props.attributes.calendar_id &&  props.attributes.calendar_id != '0' ?  props.attributes.calendar_id : false;

            return el(
                SelectControl,
                {
                    label: 'Select GrandWP Calendar',
                    value: props.attributes.calendar_id ? parseInt(props.attributes.calendar_id) : 0,
                    instanceId: 'gd-calendar-selector',
                    onChange: function (value) {
                        props.setAttributes({calendar_id: value});
                    },
                    options: gdeventcalendarblock.gdcalendar,
                }
            );

        },

        save: function (props) {
            return el('p', {style: blockStyle}, '[gd_calendar id="'+props.attributes.calendar_id+'"]');
        },
    });
})(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components
);