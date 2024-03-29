class Accordion {
    constructor(i) {
        (this.el = i),
            (this.summary = i.querySelector("summary")),
            (this.content = i.querySelector(".accordion-body")),
            (this.animation = null),
            (this.isClosing = !1),
            (this.isExpanding = !1),
            this.summary.addEventListener("click", (i) => this.onClick(i));
    }

    onClick(i) {
        i.preventDefault(),
            (this.el.style.overflow = "hidden"),
            this.isClosing || !this.el.open
                ? this.open()
                : (this.isExpanding || this.el.open) && this.shrink();
    }

    shrink() {
        this.isClosing = !0;
        let i = `${this.el.offsetHeight}px`,
            t = `${this.summary.offsetHeight}px`;
        this.animation && this.animation.cancel(),
            (this.animation = this.el.animate(
                {height: [i, t]},
                {duration: 200, easing: "ease-in-out"}
            )),
            (this.animation.onfinish = () => this.onAnimationFinish(!1)),
            (this.animation.oncancel = () => (this.isClosing = !1));
    }

    open() {
        (this.el.style.height = `${this.el.offsetHeight}px`),
            (this.el.open = !0),
            window.requestAnimationFrame(() => this.expand());
    }

    expand() {
        this.isExpanding = !0;
        let i = `${this.el.offsetHeight}px`,
            t = `${this.summary.offsetHeight + this.content.offsetHeight}px`;
        this.animation && this.animation.cancel(),
            (this.animation = this.el.animate(
                {height: [i, t]},
                {duration: 400, easing: "ease-out"}
            )),
            (this.animation.onfinish = () => this.onAnimationFinish(!0)),
            (this.animation.oncancel = () => (this.isExpanding = !1));
    }

    onAnimationFinish(i) {
        (this.el.open = i),
            (this.animation = null),
            (this.isClosing = !1),
            (this.isExpanding = !1),
            (this.el.style.height = this.el.style.overflow = "");
    }
}

document.querySelectorAll(".accordion > details.accordion-item").forEach((i) => {
    new Accordion(i);
});