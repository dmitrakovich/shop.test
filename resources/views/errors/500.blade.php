<script
  src="https://browser.sentry-cdn.com/6.9.0/bundle.min.js"
  integrity="sha384-WO22OE751vRf/HrLRHFis3ipNR16hUk5Q0qW9ascPaSswHI9Q/0ZFMaMvJ0ZgmSI"
  crossorigin="anonymous"
></script>

<div class="content">
    <div class="title">Something went wrong.</div>

    @if(app()->bound('sentry') && app('sentry')->getLastEventId())
      <div class="subtitle">Error ID: {{ app('sentry')->getLastEventId() }}</div>
      <script>
        Sentry.init({ dsn: 'https://e75029f28a974691925616a8d3c3a4a0@o923846.ingest.sentry.io/5871606' });
        Sentry.showReportDialog({
          eventId: '{{ app('sentry')->getLastEventId() }}'
        });
      </script>
    @endif
</div>
