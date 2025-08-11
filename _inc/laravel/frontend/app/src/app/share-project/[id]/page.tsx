// app/(dashboard)/projects/[id]/page.tsx
"use client";
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import axios from "axios";
import { Project } from "../../../definitions/helpers";
export default function ProjectPage() {
  const params = useParams<{ id: string }>(),
    [project, setProject] = useState<Project | null>(null);
  useEffect(() => {
    const fetchProject = async () => {
      try {
        const res = await axios.get(`/api/projects/${params.id}`);
        if (res?.data) setProject(res.data);
      } catch (err) {
        console.error(`Error fetching project: ${err}`);
      }
    };
    fetchProject();
  }, [params.id]);
  return (
    <div>
      {project ? <h1>{project.name}</h1> : <div>Loading project...</div>}
    </div>
  );
}
