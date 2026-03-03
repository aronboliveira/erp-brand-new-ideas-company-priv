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
    - img "404 Error"
    - paragraph [ref=e12]: We encountered a technical issue while processing your request. Don't worry, we're working to fix it!
    - generic [ref=e13]:
      - generic [ref=e14]: "8"
      - text: Automatically redirecting in seconds
      - generic [ref=e15]:
        - progressbar
    - generic [ref=e16]:
      - link "Go Back Now" [ref=e17] [cursor=pointer]:
        - /url: http://localhost:8000/account-dashboard
        - img [ref=e18]
        - generic [ref=e20]: Go Back Now
      - link "Home Page" [ref=e21] [cursor=pointer]:
        - /url: http://localhost:8000
        - img [ref=e22]
        - generic [ref=e25]: Home Page
    - generic [ref=e26]:
      - img [ref=e27]
      - generic [ref=e29]: Press ESC to cancel automatic redirect
```