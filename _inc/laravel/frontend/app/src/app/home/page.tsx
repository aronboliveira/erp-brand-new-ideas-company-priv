// app/(landing)/page.tsx
"use client";
import { useEffect } from "react";
export default function LandingPage() {
  useEffect(() => {
    import("bootstrap/dist/js/bootstrap.bundle.min.js");
    import("wowjs").then(WOW => new WOW.WOW({ live: false }).init());
  }, []);
  return (
    <>
      <header id='home' className='bg-primary text-white py-5'>
        <div className='container'>
          <div className='row align-items-center'>
            <div className='col-sm-5'>
              <h1>ERP Brand New Ideas Company</h1>
              <h2>
                ERP de Negócios completo, com recursos de CRM, HRM. Gerencie
                suas equipes e processos com excelência!
              </h2>
              <p>
                Use these awesome forms to login or create new account in your
                project for free.
              </p>
              <a href='/login' className='btn btn-light me-2'>
                Live Demo
              </a>
              <a href='#' className='btn btn-outline-light'>
                Buy now
              </a>
            </div>
            <div className='col-sm-5'>
              <img
                src='/assets/images/front/header-mokeup.svg'
                alt='ERP Brand New Ideas Company'
                className='img-fluid'
              />
            </div>
          </div>
        </div>
      </header>

      <section id='features' className='py-5'>
        <div className='container'>
          <h2>Features</h2>
          <div className='row'>
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className='col-lg-3 col-md-6'>
                <div className='card my-3'>
                  <div className='card-body text-center'>
                    <i className='ti ti-report-money fs-1 mb-3'></i>
                    <h4>Feature</h4>
                    <p>
                      Use these awesome forms to login or create new account in
                      your project for free.
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id='pricing' className='py-5 bg-light'>
        <div className='container'>
          <h2>Pricing</h2>
          <div className='row'>
            {[59, 59, 119].map((price, i) => (
              <div key={i} className='col-md-4'>
                <div
                  className={`card price-card ${
                    i === 1 ? "bg-primary text-white" : ""
                  } my-3`}
                >
                  <div className='card-body text-center'>
                    <span className='badge bg-primary'>STARTER</span>
                    <h3 className='my-3'>${price}/month</h3>
                    <ul className='list-unstyled'>
                      <li>2 team members</li>
                      <li>20GB Cloud storage</li>
                      <li>Integration help</li>
                    </ul>
                    <button
                      className={`btn ${i === 1 ? "btn-light" : "btn-primary"}`}
                    >
                      Start with plan
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id='faq' className='py-5'>
        <div className='container'>
          <h2>Frequently Asked Questions</h2>
          <div className='accordion' id='faqAccordion'>
            {[
              "How do I order?",
              "How to get support?",
              "What is included?",
            ].map((q, idx) => (
              <div className='accordion-item' key={idx}>
                <h2 className='accordion-header'>
                  <button
                    className={`accordion-button ${idx ? "collapsed" : ""}`}
                    type='button'
                    data-bs-toggle='collapse'
                    data-bs-target={`#faq${idx}`}
                  >
                    {q}
                  </button>
                </h2>
                <div
                  id={`faq${idx}`}
                  className={`accordion-collapse collapse ${
                    !idx ? "show" : ""
                  }`}
                >
                  <div className='accordion-body'>
                    Use these awesome forms to login or create new account in
                    your project for free.
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
