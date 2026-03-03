# Page snapshot

```yaml
- generic [ref=e3]:
  - generic:
    - generic:
      - img
    - generic:
      - img
    - generic:
      - img
  - generic [ref=e4]:
    - generic [ref=e5]:
      - text: "404"
      - img [ref=e6]
    - heading "Server Error" [level=1] [ref=e8]
    - paragraph [ref=e9]: Something went wrong on our end
  - generic [ref=e10]:
    - img "404 Error" [ref=e11]
    - paragraph [ref=e13]: We encountered a technical issue while processing your request. Don't worry, we're working to fix it!
    - generic [ref=e14]:
      - generic [ref=e15]: "8"
      - text: Automatically redirecting in seconds
      - generic [ref=e16]:
        - progressbar
    - generic [ref=e17]:
      - link "Go Back Now" [ref=e18] [cursor=pointer]:
        - /url: http://localhost:8000/account-dashboard
        - img [ref=e19]
        - generic [ref=e21]: Go Back Now
      - link "Home Page" [ref=e22] [cursor=pointer]:
        - /url: http://localhost:8000
        - img [ref=e23]
        - generic [ref=e26]: Home Page
    - generic [ref=e27]:
      - img [ref=e28]
      - generic [ref=e30]: Press ESC to cancel automatic redirect
```