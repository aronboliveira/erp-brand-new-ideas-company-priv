// pages/system-check.tsx
import React, { useEffect, useState } from "react";
import {
  Container,
  Typography,
  Box,
  Button,
  List,
  ListItem,
  ListItemText,
  Paper,
} from "@material-ui/core";
import Link from "next/link";
import { toast } from "react-toastify";
import { SystemCheckData } from "../../definitions/components";
export default function SystemCheckPage() {
  const [data, setData] = useState<SystemCheckData | null>(null),
    [loading, setLoading] = useState<boolean>(true);
  useEffect(() => {
    async function fetchData() {
      try {
        const res = await fetch("/system-check-api/");
        if (!res.ok) throw new Error(`Error fetching data: ${res.status}`);
        const json = await res.json();
        setData(json);
      } catch (error: any) {
        console.error("Error fetching system check:", error);
        toast.error(`Error fetching system check: ${error.message}`);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, []);
  if (loading) {
    return (
      <Container maxWidth='sm'>
        <Box p={2}>
          <Typography>Loading system check...</Typography>
        </Box>
      </Container>
    );
  }
  if (!data) {
    return (
      <Container maxWidth='sm'>
        <Box p={2}>
          <Typography color='error'>
            Unable to load system check data.
          </Typography>
        </Box>
      </Container>
    );
  }
  return (
    <Container maxWidth='sm'>
      <Box my={4}>
        <Typography variant='h4' gutterBottom>
          System Configuration Check
        </Typography>
        <Box mb={2}>
          <Typography variant='body1'>
            <strong>Required Python version:</strong> {data.requiredPython}
          </Typography>
          <Typography variant='body1'>
            <strong>Current Python version:</strong> {data.pythonCurrentVersion}{" "}
            {data.pythonAllowed ? (
              <span style={{ color: "green" }}>[OK]</span>
            ) : (
              <span style={{ color: "red" }}>[Not OK]</span>
            )}
          </Typography>
        </Box>
        <Paper style={{ padding: "1rem", marginBottom: "1rem" }}>
          <List disablePadding>
            {data.folderResults.map(item => (
              <ListItem
                key={item.folderKey}
                style={{
                  backgroundColor: item.ok ? "#d4edda" : "#f8d7da",
                  marginBottom: "0.5rem",
                  border: "1px solid #ccc",
                }}
              >
                <ListItemText
                  primary={
                    <>
                      <strong>{item.folderKey}</strong> — current perms:{" "}
                      {item.permission}{" "}
                      {item.ok ? (
                        <span style={{ color: "green" }}>[OK]</span>
                      ) : (
                        <span style={{ color: "red" }}>[Required 777]</span>
                      )}
                    </>
                  }
                />
              </ListItem>
            ))}
          </List>
        </Paper>
        {data.hasError ? (
          <Typography color='error'>
            Please fix the issues above and refresh the page.
          </Typography>
        ) : (
          <Box mt={2}>
            <Link href='/' passHref>
              <Button variant='contained' color='primary'>
                Proceed
              </Button>
            </Link>
          </Box>
        )}
      </Box>
    </Container>
  );
}
