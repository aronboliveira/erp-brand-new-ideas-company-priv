import React, { useState, useEffect, useMemo, KeyboardEvent } from "react";
import {
  Container,
  Typography,
  Card,
  CardContent,
  TextField,
  Grid,
  Box,
  Button,
  Paper,
  Avatar,
  CircularProgress,
  LinearProgress,
  Table,
  TableHead,
  TableRow,
  TableBody,
  TableCell,
} from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import Swal from "sweetalert2";
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  CartesianGrid,
} from "recharts";
import { ProjectDashboardProps } from "../../../definitions/components";
import { postToDo } from "../fetch/POST";
import { getDashboardFiltered } from "../fetch/GET";
import { deleteToDo } from "../fetch/DELETE";
import { putToDo } from "../fetch/PUT";
import { ErrorBoundary } from "../../../../node_modules/react-error-boundary/dist";
import AdminPage from "../page";
export default function Dashboard({
  userType,
  homeData,
}: ProjectDashboardProps) {
  const [keyword, setKeyword] = useState(""),
    [todos, setTodos] = useState(homeData.todo ? homeData.todo : []),
    {
      data: dashboardHtml,
      isLoading: isDashboardLoading,
      error: dashboardError,
      refetch: refetchDashboard,
    } = useQuery({
      queryKey: ["dashboardView", keyword],
      queryFn: () => getDashboardFiltered(keyword),
      enabled: userType === "admin",
      placeholderData: (prev: any) => prev,
    });
  useEffect(() => {
    userType === "admin" && refetchDashboard();
  }, [keyword, userType, refetchDashboard]);
  const handleKeywordChange = (e: React.ChangeEvent<HTMLInputElement>) =>
      setKeyword(e.target.value),
    taskOverviewData = useMemo(() => {
      if (!homeData.task_overview) return [];
      return Object.entries(homeData.task_overview).map(([name, value]) => ({
        name,
        value,
      }));
    }, [homeData.task_overview]),
    timesheetData = useMemo(() => {
      if (!homeData.timesheet_logged) return [];
      return Object.entries(homeData.timesheet_logged).map(([name, value]) => ({
        name,
        value,
      }));
    }, [homeData.timesheet_logged]),
    handleTodoSubmit = async (e: KeyboardEvent<HTMLInputElement>) => {
      if (e.key !== "Enter") return;
      const title = e.currentTarget.value.trim();
      if (!title) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Please write todo title!",
        });
        return;
      }
      postToDo(title, e, setTodos);
    },
    handleTodoCheckbox = async (todoId: string, updateUrl: string) =>
      putToDo({ id: todoId.toString(), url: updateUrl, dispatch: setTodos }),
    handleTodoDelete = async (todoId: string, deleteUrl: string) =>
      deleteToDo({ id: todoId.toString(), url: deleteUrl, dispatch: setTodos }),
    getStatusColor = (status: string) => {
      const colorMap: { [key: string]: string } = {
        completed: "green",
        pending: "orange",
        canceled: "red",
      };
      return colorMap[status] || "gray";
    };
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <AdminPage>
        <Container maxWidth='md'>
          <Typography variant='h4' gutterBottom>
            Project Dashboard
          </Typography>
          {userType === "admin" ? (
            <Box mb={3}>
              <TextField
                id='keyword'
                label='Search by Name or Skill'
                variant='outlined'
                fullWidth
                value={keyword}
                onChange={handleKeywordChange}
              />
              {isDashboardLoading ? (
                <Typography>Loading dashboard view...</Typography>
              ) : dashboardError ? (
                <Typography color='error'>
                  Error loading dashboard view.
                </Typography>
              ) : (
                <Box
                  id='dashboard_view'
                  dangerouslySetInnerHTML={{ __html: dashboardHtml || "" }}
                />
              )}
            </Box>
          ) : (
            <>
              <Grid container spacing={2}>
                {[
                  { label: "Total Projects", data: homeData.total_project },
                  { label: "Total Tasks", data: homeData.total_task },
                  { label: "Total Expense", data: homeData.total_expense },
                  {
                    label: "Total Users",
                    data: { total: homeData.total_user },
                  },
                ].map((item, idx) => (
                  <Grid item xs={12} sm={6} md={3} key={idx}>
                    <Card>
                      <CardContent>
                        <Typography variant='subtitle1'>
                          {item.label}
                        </Typography>
                        <Typography variant='h5'>
                          {item.data?.total || 0}
                        </Typography>
                        {item.data &&
                          "percentage" in item.data &&
                          item.data?.percentage !== undefined && (
                            <>
                              <Typography color='textSecondary'>
                                {item.data.percentage || 0}%
                              </Typography>
                              <Box
                                sx={{
                                  position: "relative",
                                  display: "inline-flex",
                                  mt: 1,
                                }}
                              >
                                <CircularProgress
                                  variant='determinate'
                                  value={item.data.percentage || 0}
                                  size={40}
                                  color='primary'
                                />
                                <Box
                                  sx={{
                                    top: 0,
                                    left: 0,
                                    bottom: 0,
                                    right: 0,
                                    position: "absolute",
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                  }}
                                >
                                  <Typography
                                    variant='caption'
                                    component='div'
                                    color='textSecondary'
                                  >
                                    {`${Math.round(
                                      item.data.percentage || 0
                                    )}%`}
                                  </Typography>
                                </Box>
                              </Box>
                            </>
                          )}
                      </CardContent>
                    </Card>
                  </Grid>
                ))}
              </Grid>
              <Box my={4}>
                <Typography variant='h6' gutterBottom>
                  Tasks Overview
                </Typography>
                <ResponsiveContainer width='100%' height={280}>
                  <LineChart data={taskOverviewData}>
                    <CartesianGrid strokeDasharray='3 3' />
                    <XAxis dataKey='name' />
                    <YAxis />
                    <Tooltip />
                    <Line
                      type='monotone'
                      dataKey='value'
                      stroke='#8884d8'
                      strokeWidth={2}
                    />
                  </LineChart>
                </ResponsiveContainer>
              </Box>
              <Box my={4}>
                <Typography variant='h6' gutterBottom>
                  Timesheet Logged Hours
                </Typography>
                <ResponsiveContainer width='100%' height={280}>
                  <BarChart data={timesheetData}>
                    <CartesianGrid strokeDasharray='3 3' />
                    <XAxis dataKey='name' />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey='value' fill='#82ca9d' />
                  </BarChart>
                </ResponsiveContainer>
              </Box>
              <Box my={4}>
                <Typography variant='h6' gutterBottom>
                  Project Status
                </Typography>
                <Grid container spacing={2}>
                  {homeData.project_status &&
                    Object.entries(homeData.project_status).map(
                      ([status, info]) => (
                        <Grid item xs={12} sm={6} key={status}>
                          <Card>
                            <CardContent>
                              <Typography variant='subtitle2'>
                                {status}
                              </Typography>
                              <Typography
                                sx={{ color: getStatusColor(status) }}
                              >
                                {info.percentage}% ({info.total})
                              </Typography>
                              <Box sx={{ mt: 1 }}>
                                <LinearProgress
                                  variant='determinate'
                                  value={info.percentage || 0}
                                />
                              </Box>
                            </CardContent>
                          </Card>
                        </Grid>
                      )
                    )}
                </Grid>
                <Box mt={2} display='flex' justifyContent='center'>
                  {homeData.project_status &&
                    Object.entries(homeData.project_status).map(
                      ([status, info]) => (
                        <Box
                          key={status}
                          sx={{ flex: 1, textAlign: "center", mx: 1 }}
                        >
                          <Typography variant='caption'>
                            <span
                              style={{
                                background: getStatusColor(status),
                                borderRadius: 50,
                                padding: "2px 6px",
                                color: "#fff",
                              }}
                            >
                              {info.total}
                            </span>
                          </Typography>
                        </Box>
                      )
                    )}
                </Box>
              </Box>
              <Box my={4}>
                <Typography variant='h6' gutterBottom>
                  Top Due Projects
                </Typography>
                {homeData.due_project && homeData.due_project.length > 0 ? (
                  <Grid container spacing={2}>
                    {homeData.due_project.map((project: any) => (
                      <Grid item xs={12} sm={6} key={project.id}>
                        <Card>
                          <CardContent>
                            <Box
                              display='flex'
                              alignItems='center'
                              justifyContent='space-between'
                            >
                              <Avatar
                                src={project.img_image || undefined}
                                alt={project.name}
                                sx={{ mr: 2 }}
                              />
                              <Box flexGrow={1}>
                                <Typography variant='subtitle1'>
                                  {project.name}
                                </Typography>
                                <LinearProgress
                                  variant='determinate'
                                  value={
                                    project.project_progress().percentage || 0
                                  }
                                  sx={{ height: 6, borderRadius: 3, mt: 1 }}
                                />
                              </Box>
                              <Box ml={2}>
                                <Typography
                                  variant='caption'
                                  sx={{
                                    color:
                                      project.userRole === "Owner"
                                        ? "green"
                                        : "orange",
                                  }}
                                >
                                  {project.userRole}
                                </Typography>
                              </Box>
                            </Box>
                          </CardContent>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                ) : (
                  <Typography>No Due Projects Found.</Typography>
                )}
              </Box>
              <Box my={4}>
                <Typography variant='h6' gutterBottom>
                  Top Due Tasks
                </Typography>
                {homeData.due_tasks && homeData.due_tasks.length > 0 ? (
                  <Paper>
                    <Table>
                      <TableHead>
                        <TableRow>
                          <TableCell>Tasks</TableCell>
                          <TableCell>Project</TableCell>
                          <TableCell>Stage</TableCell>
                          <TableCell>Completion</TableCell>
                        </TableRow>
                      </TableHead>
                      <TableBody>
                        {homeData.due_tasks.map((task: any) => (
                          <TableRow key={task.id}>
                            <TableCell>
                              <a href={`/projects/${task.project.id}/tasks`}>
                                {task.name}
                              </a>
                            </TableCell>
                            <TableCell>{task.project.name}</TableCell>
                            <TableCell>
                              <span
                                style={{
                                  background: "#eee",
                                  padding: "2px 6px",
                                  borderRadius: 4,
                                }}
                              >
                                {task.priority}
                              </span>
                            </TableCell>
                            <TableCell>
                              {task.taskProgress(task).percentage}%
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </Paper>
                ) : (
                  <Typography>No Due Tasks Found.</Typography>
                )}
              </Box>
              <Box my={4}>
                <Typography variant='h6' gutterBottom>
                  To do list
                </Typography>
                <TextField
                  id='todo-input'
                  placeholder='Enter todo title and press Enter'
                  fullWidth
                  variant='outlined'
                  onKeyDown={handleTodoSubmit}
                />
                <Box mt={2}>
                  {todos && todos.length > 0 ? (
                    todos.map((todo: any) => (
                      <Card key={todo.id} variant='outlined' sx={{ mb: 1 }}>
                        <CardContent
                          sx={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <Box
                            onClick={() =>
                              handleTodoCheckbox(todo.id, todo.updateUrl)
                            }
                            sx={{
                              textDecoration: todo.is_complete
                                ? "line-through"
                                : "none",
                              cursor: "pointer",
                            }}
                          >
                            <Typography>{todo.title}</Typography>
                          </Box>
                          <Button
                            color='error'
                            onClick={() =>
                              handleTodoDelete(todo.id, todo.deleteUrl)
                            }
                          >
                            Delete
                          </Button>
                        </CardContent>
                      </Card>
                    ))
                  ) : (
                    <Typography>No Todo List Found.</Typography>
                  )}
                </Box>
              </Box>
            </>
          )}
        </Container>
      </AdminPage>
    </ErrorBoundary>
  );
}
