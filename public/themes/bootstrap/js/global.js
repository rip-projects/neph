/** grid component **/
!function($) {

    var Grid = function($el, options) {
        this.$el = $($el);
        this.options = options;
    }

    Grid.prototype.select = function($el) {
        $($el).parents('tr:first').toggleClass('selected');
    };

    Grid.prototype.getSelected = function() {
        var ids = [];
        this.$el.find('.grid-row-checkbox input[type=checkbox]:checked').each(function() {
            ids.push($(this).val())
        });
        return ids;
    }

    $.fn.grid = function(options) {
        if (!$(this).data('grid')) {
            $(this).data('grid', new Grid(this));
        }
        return $(this).data('grid');
    }

    $(document).on('mouseenter.grid', 'table.grid tr.grid-row', function() {
        $(this).addClass('hover');
    });

    $(document).on('mouseleave.grid', 'table.grid tr.grid-row', function() {
        $(this).removeClass('hover');
    });

    $(document).on('click.grid', 'table.grid tr.grid-row', function(evt) {
        $(this).find('.grid-row-checkbox input[type=checkbox]').trigger('click.grid');
    });

    $(document).on('click.grid', 'table.grid input[type=checkbox]', function(evt) {
        evt.stopImmediatePropagation();

        $(this).parents('table.grid:first').grid().select(this);
    });

    $(document).on('click.grid', 'a[data-grid]', function(evt) {
        evt.preventDefault();

        var selected = $($(this).attr('data-grid')).grid().getSelected();

        location.href = $(this).attr('href') + '/' + selected.join(',');
    });

}(window.jQuery);