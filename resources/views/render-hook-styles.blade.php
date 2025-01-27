<style>
    /* General */
    .container {
        min-width: 100%;
        position: sticky;
        top: 0px;
        z-index: 50;
    }

    /* Trial countdown styles */
    .trial {
        display: flex;
        align-items: center;
        justify-content: space-evenly;
        padding: 1rem 0rem;
        background-color: #fca311;
        font-weight: 500;
    }

    .trial:is(.dark *) {
        background-color: #372fa2;
    }

    a.group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 1.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        font-weight: 500;
        outline: 2px solid transparent;
        outline-offset: 2px;
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
        background-color: #fff;
    }

    a.group:hover {
        background-color: #000;
        color: #fff;
    }

    a.group:is(.dark *) {
        background-color: #000;
    }

    a.group:hover:is(.dark *) {
        background-color: #fff;
        color: #000;
    }

    .fire {
        display: inline;
        height: 1rem;
        width: 1rem;
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }

    .fire:is(.dark *) {
        color: #fff;
    }

    a.group:hover .fire {
        color: #fca311;
    }

    a.group:hover .fire:is(.dark *) {
        color: #3730a3;
    }

    /* Complete KYC stlyes */

    .kyc {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 0rem;
        background-color: #fca311;
        font-weight: 500;
    }

    .kyc:is(.dark *) {
        background-color: #372fa2;
    }

    a.profile-link {
        text-decoration: underline;
        font-weight: 600;
        font-size: 0.875rem;
        line-height: 1.25rem
    }
</style>