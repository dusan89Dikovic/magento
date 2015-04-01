var magalterDayDealCounter = Class.create({
    initialize: function(obj, container) {
        this.time_left = obj.timeLeft;
        this.container = container;
        this.keep_counting = true;
        this.timer();
    },
    countdown: function() {
        if (this.time_left < 2) {
            this.keep_counting = 0;
        }
        this.time_left = this.time_left - 1;
    },
    add_leading_zero: function(n) {
        if (n.toString().length < 2) {
            return '0' + n;
        } else {
            return n;
        }
    },
    format_output: function() {
        var days, hours, minutes, seconds;
        seconds = this.time_left % 60;
        minutes = Math.floor(this.time_left / 60) % 60;
        hours = Math.floor(this.time_left / 3600) % 24;
        days = Math.floor(this.time_left / (3600 * 24));
        days = this.add_leading_zero(days);
        seconds = this.add_leading_zero(seconds);
        minutes = this.add_leading_zero(minutes);
        hours = this.add_leading_zero(hours);
        return '<span class = "sep-day" >' + days + '</span>' + '<span class = "sep-hour" >' + hours + '</span>' + '<span class = "sep-minute" >' + minutes + '</span>' + '<span class = "sep-second" >' + seconds + '</span>';
    },
    show_time_left: function() {
        document.getElementById(this.container).innerHTML = this.format_output();
    },
    count: function() {
        this.countdown();
        this.show_time_left();
    },
    timer: function() {
        this.count();
        if (this.keep_counting) {
            setTimeout(function() {
                this.timer();
            }.bind(this), 1000);
        }
    }
});
